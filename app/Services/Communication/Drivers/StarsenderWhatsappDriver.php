<?php

namespace App\Services\Communication\Drivers;

use App\Models\CommunicationDelivery;
use App\Services\Communication\CommunicationDriver;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class StarsenderWhatsappDriver implements CommunicationDriver
{
    public function send(CommunicationDelivery $delivery): array
    {
        if (! $this->ready()) {
            throw new RuntimeException($this->readinessMessage());
        }

        $response = Http::acceptJson()
            ->withHeaders(['Authorization' => (string) config('communications.whatsapp.starsender.api_key')])
            ->timeout((int) config('communications.timeout', 15))
            ->retry(2, 500, throw: false)
            ->post(rtrim((string) config('communications.whatsapp.starsender.base_url'), '/').'/api/send', [
                'messageType' => 'text',
                'to' => $delivery->recipient_address,
                'body' => $delivery->content,
                'delay' => (int) config('communications.whatsapp.starsender.delay_seconds', 1),
            ]);

        $json = $response->json();
        if (! $response->successful() || Arr::get($json, 'success') === false) {
            throw new RuntimeException('StarSender menolak pengiriman (HTTP '.$response->status().').');
        }

        return [
            'provider_message_id' => $this->messageId(is_array($json) ? $json : []),
            'metadata' => ['http_status' => $response->status()],
        ];
    }

    public function ready(): bool
    {
        return filled(config('communications.whatsapp.starsender.api_key'))
            && filled(config('communications.whatsapp.starsender.base_url'));
    }

    public function readinessMessage(): string
    {
        return $this->ready()
            ? 'API key StarSender tersedia.'
            : 'STARSENDER_API_KEY belum diisi di Environment Variables Coolify.';
    }

    /** @param array<string,mixed> $json */
    private function messageId(array $json): ?string
    {
        $value = Arr::get($json, 'data.id')
            ?? Arr::get($json, 'data.messageId')
            ?? Arr::get($json, 'messageId');

        return is_scalar($value) ? (string) $value : null;
    }
}
