<?php

namespace App\Services\Communication\Drivers;

use App\Models\CommunicationDelivery;
use App\Services\Communication\CommunicationDriver;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GenericWebhookWhatsappDriver implements CommunicationDriver
{
    public function send(CommunicationDelivery $delivery): array
    {
        if (! $this->ready()) {
            throw new RuntimeException($this->readinessMessage());
        }

        $headers = [];
        $authorizationHeader = trim((string) config('communications.whatsapp.generic.authorization_header'));
        if (filled(config('communications.whatsapp.generic.token')) && $authorizationHeader !== '') {
            $headers[$authorizationHeader] = (string) config('communications.whatsapp.generic.token');
        }

        $recipientField = $this->field('recipient_field', 'to');
        $messageField = $this->field('message_field', 'message');
        $referenceField = $this->field('reference_field', 'reference_id');
        $tokenField = $this->field('token_field');
        $payload = [
            $recipientField => $delivery->recipient_address,
            $messageField => $delivery->content,
        ];
        if ($referenceField) {
            $payload[$referenceField] = (string) $delivery->id;
        }
        if ($tokenField && filled(config('communications.whatsapp.generic.token'))) {
            $payload[$tokenField] = (string) config('communications.whatsapp.generic.token');
        }

        $request = Http::acceptJson()
            ->withHeaders($headers)
            ->timeout((int) config('communications.timeout', 15))
            ->retry(2, 500, throw: false);
        if (strtolower((string) config('communications.whatsapp.generic.format')) === 'form') {
            $request = $request->asForm();
        }
        $response = $request->post((string) config('communications.whatsapp.generic.endpoint'), $payload);

        if (! $response->successful()) {
            throw new RuntimeException('Provider WhatsApp menolak pengiriman (HTTP '.$response->status().').');
        }

        $json = $response->json();
        $messageId = is_array($json)
            ? (Arr::get($json, 'data.id') ?? Arr::get($json, 'id') ?? Arr::get($json, 'message_id'))
            : null;

        return [
            'provider_message_id' => is_scalar($messageId) ? (string) $messageId : null,
            'metadata' => ['http_status' => $response->status()],
        ];
    }

    public function ready(): bool
    {
        return filter_var(config('communications.whatsapp.generic.endpoint'), FILTER_VALIDATE_URL) !== false;
    }

    public function readinessMessage(): string
    {
        return $this->ready()
            ? 'Endpoint WhatsApp generik tersedia.'
            : 'WHATSAPP_WEBHOOK_ENDPOINT belum berupa URL yang valid.';
    }

    private function field(string $key, ?string $fallback = null): ?string
    {
        $value = trim((string) config('communications.whatsapp.generic.'.$key, $fallback));

        return $value !== '' && preg_match('/^[A-Za-z0-9_.-]+$/', $value) ? $value : $fallback;
    }
}
