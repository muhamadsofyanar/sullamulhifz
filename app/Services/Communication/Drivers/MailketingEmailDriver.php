<?php

namespace App\Services\Communication\Drivers;

use App\Models\CommunicationDelivery;
use App\Services\Communication\CommunicationDriver;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class MailketingEmailDriver implements CommunicationDriver
{
    public function send(CommunicationDelivery $delivery): array
    {
        if (! $this->ready()) {
            throw new RuntimeException($this->readinessMessage());
        }

        $response = Http::asForm()
            ->acceptJson()
            ->timeout((int) config('communications.timeout', 15))
            ->retry(2, 500, throw: false)
            ->post((string) config('communications.email.mailketing.endpoint'), [
                'api_token' => (string) config('communications.email.mailketing.api_token'),
                'from_name' => (string) config('mail.from.name'),
                'from_email' => (string) config('mail.from.address'),
                'recipient' => $delivery->recipient_address,
                'subject' => $delivery->subject ?: config('app.name'),
                'content' => $delivery->content,
            ]);

        $json = $response->json();
        $providerStatus = is_array($json) ? strtolower((string) ($json['status'] ?? '')) : '';
        if (! $response->successful() || $providerStatus !== 'success') {
            throw new RuntimeException('Mailketing menolak pengiriman (HTTP '.$response->status().').');
        }

        return [
            'provider_message_id' => null,
            'metadata' => ['http_status' => $response->status(), 'provider_status' => $providerStatus],
        ];
    }

    public function ready(): bool
    {
        $from = (string) config('mail.from.address');

        return filled(config('communications.email.mailketing.api_token'))
            && filter_var($from, FILTER_VALIDATE_EMAIL) !== false
            && $from !== 'hello@example.com';
    }

    public function readinessMessage(): string
    {
        return $this->ready()
            ? 'API token Mailketing dan alamat pengirim tersedia.'
            : 'MAILKETING_API_TOKEN dan MAIL_FROM_ADDRESS yang valid wajib diisi di Coolify.';
    }
}
