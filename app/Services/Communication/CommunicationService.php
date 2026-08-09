<?php

namespace App\Services\Communication;

use App\Jobs\SendCommunicationDelivery;
use App\Models\CommunicationDelivery;
use App\Models\CommunicationTemplate;
use App\Models\IntegrationConnection;
use App\Models\LiaisonMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class CommunicationService
{
    public function __construct(
        private readonly CommunicationDriverFactory $drivers,
        private readonly CommunicationTemplateRenderer $renderer,
    ) {
    }

    /** @return array{ready:bool,message:string,driver:string} */
    public function readiness(IntegrationConnection $connection): array
    {
        $driverName = (string) data_get($connection->configuration, 'driver', 'log');
        try {
            $driver = $this->drivers->make($connection->provider, $driverName);

            return ['ready' => $driver->ready(), 'message' => $driver->readinessMessage(), 'driver' => $driverName];
        } catch (Throwable) {
            return ['ready' => false, 'message' => 'Driver yang tersimpan tidak dikenali.', 'driver' => $driverName];
        }
    }

    public function connection(int $institutionId, string $channel, bool $activeOnly = true): ?IntegrationConnection
    {
        return IntegrationConnection::query()
            ->where('institution_id', $institutionId)
            ->where('provider', $channel)
            ->when($activeOnly, fn ($query) => $query->where('status', 'active'))
            ->first();
    }

    /** @param array<string,mixed> $attributes */
    public function send(int $institutionId, string $channel, string $address, string $content, array $attributes = []): CommunicationDelivery
    {
        $connection = $this->connection($institutionId, $channel);
        if (! $connection) {
            throw new RuntimeException('Kanal '.strtoupper($channel).' belum aktif.');
        }

        $driver = (string) data_get($connection->configuration, 'driver', 'log');
        $address = $channel === 'whatsapp' ? $this->normalizePhone($address) : Str::lower(trim($address));
        $idempotencyKey = $attributes['idempotency_key'] ?? null;

        if ($idempotencyKey) {
            $existing = CommunicationDelivery::query()->where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }
        }

        $delivery = CommunicationDelivery::create([
            'institution_id' => $institutionId,
            'direction' => 'outbound',
            'channel' => $channel,
            'provider' => $driver,
            'event_key' => $attributes['event_key'] ?? 'manual',
            'recipient_user_id' => $attributes['recipient_user_id'] ?? null,
            'recipient_name' => $attributes['recipient_name'] ?? null,
            'recipient_address' => $address,
            'subject' => $attributes['subject'] ?? null,
            'content' => $content,
            'status' => 'queued',
            'idempotency_key' => $idempotencyKey,
            'queued_at' => now(),
            'metadata' => $attributes['metadata'] ?? null,
            'created_by_user_id' => $attributes['created_by_user_id'] ?? null,
        ]);

        $this->dispatch($delivery);

        return $delivery->fresh();
    }

    public function retry(CommunicationDelivery $delivery): CommunicationDelivery
    {
        if (! $delivery->isRetryable()) {
            throw new RuntimeException('Pesan ini tidak dapat dikirim ulang.');
        }

        $delivery->update(['status' => 'queued', 'queued_at' => now(), 'failed_at' => null, 'last_error' => null]);
        $this->dispatch($delivery);

        return $delivery->fresh();
    }

    public function notifyLiaison(LiaisonMessage $message): void
    {
        $message->loadMissing(['thread.student.guardians.user', 'sender']);
        $thread = $message->thread;
        if (! $thread) {
            return;
        }

        $participantIds = DB::table('liaison_participants')
            ->where('liaison_thread_id', $thread->id)
            ->where('user_id', '!=', $message->sender_user_id)
            ->pluck('user_id');

        $recipients = User::query()->whereIn('id', $participantIds)->where('status', 'active')->get();
        foreach ($recipients as $recipient) {
            if ($recipient->hasRole('guardian')) {
                $guardian = $thread->student?->guardians->firstWhere('user_id', $recipient->id);
                if ($guardian && ! (bool) $guardian->pivot->can_receive_notifications) {
                    continue;
                }
            }

            $variables = [
                'recipient_name' => $recipient->name,
                'student_name' => $thread->student?->full_name ?: 'santri',
                'sender_name' => $message->sender?->name ?: 'pengelola',
                'subject' => $thread->subject,
                'portal_url' => rtrim((string) config('sullam.portal_base_url'), '/').'/buku-penghubung/'.$thread->id,
            ];

            $this->sendEventSafely('liaison', $recipient, $variables, 'liaison:'.$message->id);
        }
    }

    public function sendAccountInvitation(User $recipient, string $activationUrl, ?int $createdByUserId = null): ?CommunicationDelivery
    {
        $variables = [
            'recipient_name' => $recipient->name,
            'activation_url' => $activationUrl,
            'expires_hours' => '48',
        ];

        foreach (['email', 'whatsapp'] as $channel) {
            $address = $channel === 'email' ? $recipient->email : $recipient->phone;
            $connection = $recipient->institution_id ? $this->connection((int) $recipient->institution_id, $channel) : null;
            if (! $address || ! $connection || ! $this->eventEnabled($connection, 'account_invitation')) {
                continue;
            }

            $template = $this->template((int) $recipient->institution_id, $channel, 'account_invitation');
            if (! $template) {
                continue;
            }
            $rendered = $this->renderer->render($template, $variables);

            return $this->send((int) $recipient->institution_id, $channel, $address, $rendered['content'], [
                'event_key' => 'account_invitation',
                'subject' => $rendered['subject'],
                'recipient_user_id' => $recipient->id,
                'recipient_name' => $recipient->name,
                'created_by_user_id' => $createdByUserId,
            ]);
        }

        return null;
    }

    public function sendPasswordReset(User $recipient, string $resetUrl): ?CommunicationDelivery
    {
        if (! $recipient->institution_id || ! $recipient->email) {
            return null;
        }

        $connection = $this->connection((int) $recipient->institution_id, 'email');
        if (! $connection || ! $this->eventEnabled($connection, 'password_reset')) {
            return null;
        }

        $template = $this->template((int) $recipient->institution_id, 'email', 'password_reset');
        if (! $template) {
            return null;
        }

        $rendered = $this->renderer->render($template, [
            'recipient_name' => $recipient->name,
            'reset_url' => $resetUrl,
            'expires_minutes' => (string) config('auth.passwords.users.expire', 60),
        ]);

        return $this->send((int) $recipient->institution_id, 'email', $recipient->email, $rendered['content'], [
            'event_key' => 'password_reset',
            'subject' => $rendered['subject'],
            'recipient_user_id' => $recipient->id,
            'recipient_name' => $recipient->name,
        ]);
    }

    private function sendEventSafely(string $event, User $recipient, array $variables, string $idempotencyPrefix): void
    {
        foreach (['whatsapp', 'email'] as $channel) {
            $address = $channel === 'whatsapp' ? $recipient->phone : $recipient->email;
            $connection = $recipient->institution_id ? $this->connection((int) $recipient->institution_id, $channel) : null;
            if (! $address || ! $connection || ! $this->eventEnabled($connection, $event)) {
                continue;
            }

            $template = $this->template((int) $recipient->institution_id, $channel, $event);
            if (! $template) {
                continue;
            }

            try {
                $rendered = $this->renderer->render($template, $variables);
                $this->send((int) $recipient->institution_id, $channel, $address, $rendered['content'], [
                    'event_key' => $event,
                    'subject' => $rendered['subject'],
                    'recipient_user_id' => $recipient->id,
                    'recipient_name' => $recipient->name,
                    'idempotency_key' => $idempotencyPrefix.':'.$channel.':'.$recipient->id,
                ]);
            } catch (Throwable $exception) {
                report($exception);
            }
        }
    }

    private function template(int $institutionId, string $channel, string $event): ?CommunicationTemplate
    {
        return CommunicationTemplate::query()
            ->where('institution_id', $institutionId)
            ->where('channel', $channel)
            ->where('event_key', $event)
            ->where('is_active', true)
            ->first();
    }

    private function eventEnabled(IntegrationConnection $connection, string $event): bool
    {
        return (bool) data_get($connection->configuration, 'events.'.$event, true);
    }

    private function dispatch(CommunicationDelivery $delivery): void
    {
        if (config('communications.dispatch_mode') === 'queue') {
            SendCommunicationDelivery::dispatch($delivery);

            return;
        }

        SendCommunicationDelivery::dispatchSync($delivery);
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: '';
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }
        if (str_starts_with($digits, '0')) {
            $digits = (string) config('communications.default_country_code', '62').substr($digits, 1);
        }
        if (strlen($digits) < 9 || strlen($digits) > 16) {
            throw new RuntimeException('Nomor WhatsApp tidak valid. Gunakan nomor aktif dengan kode negara.');
        }

        return $digits;
    }
}
