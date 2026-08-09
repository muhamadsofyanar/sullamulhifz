<?php

namespace App\Jobs;

use App\Models\CommunicationDelivery;
use App\Models\IntegrationConnection;
use App\Services\Communication\CommunicationDriverFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class SendCommunicationDelivery implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int,int> */
    public array $backoff = [60, 300, 900];

    public bool $afterCommit = true;

    public function __construct(public CommunicationDelivery $delivery)
    {
        $this->onQueue('communications');
    }

    public function handle(CommunicationDriverFactory $factory): void
    {
        $delivery = $this->delivery->fresh();
        if (! $delivery || $delivery->direction !== 'outbound' || in_array($delivery->status, ['sent', 'delivered'], true)) {
            return;
        }

        $delivery->update([
            'status' => 'sending',
            'attempts' => $delivery->attempts + 1,
            'last_error' => null,
            'failed_at' => null,
        ]);

        try {
            $result = $factory->make($delivery->channel, $delivery->provider)->send($delivery);
            $delivery->update([
                'status' => 'sent',
                'provider_message_id' => $result['provider_message_id'] ?? null,
                'metadata' => array_merge($delivery->metadata ?: [], $result['metadata'] ?? []),
                'sent_at' => now(),
                'last_error' => null,
            ]);

            IntegrationConnection::query()
                ->where('institution_id', $delivery->institution_id)
                ->where('provider', $delivery->channel)
                ->update(['last_checked_at' => now(), 'last_error' => null]);
        } catch (Throwable $exception) {
            $message = $this->sanitizedError($exception);
            $delivery->update(['status' => 'failed', 'failed_at' => now(), 'last_error' => $message]);
            IntegrationConnection::query()
                ->where('institution_id', $delivery->institution_id)
                ->where('provider', $delivery->channel)
                ->update(['last_checked_at' => now(), 'last_error' => $message]);
            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        $this->delivery->fresh()?->update([
            'status' => 'failed',
            'failed_at' => now(),
            'last_error' => $exception ? $this->sanitizedError($exception) : 'Semua percobaan pengiriman gagal.',
        ]);
    }

    private function sanitizedError(Throwable $exception): string
    {
        $message = $exception->getMessage() ?: 'Pengiriman gagal tanpa detail.';
        foreach ([
            config('communications.whatsapp.starsender.api_key'),
            config('communications.whatsapp.generic.token'),
            config('communications.email.mailketing.api_token'),
            config('mail.mailers.smtp.password'),
        ] as $secret) {
            if (is_string($secret) && $secret !== '') {
                $message = str_replace($secret, '[REDACTED]', $message);
            }
        }

        return mb_substr($message, 0, 1500);
    }
}
