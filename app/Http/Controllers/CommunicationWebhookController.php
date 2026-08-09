<?php

namespace App\Http\Controllers;

use App\Models\CommunicationDelivery;
use App\Models\IntegrationConnection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CommunicationWebhookController extends Controller
{
    public function whatsapp(Request $request, IntegrationConnection $connection): JsonResponse
    {
        abort_unless($connection->provider === 'whatsapp' && $connection->status === 'active', 404);
        $expected = (string) config('communications.webhook_secret');
        if ($expected === '') {
            return response()->json(['message' => 'Webhook belum dikonfigurasi.'], 503);
        }

        $provided = (string) ($request->header('X-Sullam-Webhook-Token') ?: $request->query('token', ''));
        abort_unless($provided !== '' && hash_equals($expected, $provided), 401, 'Token webhook tidak valid.');

        $payload = $request->all();
        $providerMessageId = $this->scalar(Arr::get($payload, 'message_id')
            ?? Arr::get($payload, 'id')
            ?? Arr::get($payload, 'data.id'));
        $status = strtolower((string) (Arr::get($payload, 'status') ?? Arr::get($payload, 'data.status') ?? ''));

        if ($providerMessageId && in_array($status, ['sent', 'delivered', 'read', 'failed'], true)) {
            $delivery = CommunicationDelivery::query()
                ->where('institution_id', $connection->institution_id)
                ->where('channel', 'whatsapp')
                ->where('provider_message_id', $providerMessageId)
                ->latest('id')
                ->first();

            if ($delivery) {
                $delivery->update([
                    'status' => $status === 'read' ? 'delivered' : $status,
                    'delivered_at' => in_array($status, ['delivered', 'read'], true) ? now() : $delivery->delivered_at,
                    'failed_at' => $status === 'failed' ? now() : null,
                ]);

                return response()->json(['ok' => true, 'kind' => 'delivery_status']);
            }
        }

        $from = $this->scalar(Arr::get($payload, 'from')
            ?? Arr::get($payload, 'phone')
            ?? Arr::get($payload, 'sender'));
        $message = $this->scalar(Arr::get($payload, 'message')
            ?? Arr::get($payload, 'body')
            ?? Arr::get($payload, 'text'));

        if (! $from || $message === null) {
            return response()->json(['message' => 'Payload webhook tidak dikenali.'], 422);
        }

        $idempotencyKey = 'wa-in:'.hash('sha256', $providerMessageId ?: json_encode([
            $from, $message, Arr::get($payload, 'timestamp'), $connection->id,
        ], JSON_UNESCAPED_UNICODE));

        $delivery = CommunicationDelivery::firstOrCreate(
            ['idempotency_key' => $idempotencyKey],
            [
                'institution_id' => $connection->institution_id,
                'direction' => 'inbound',
                'channel' => 'whatsapp',
                'provider' => (string) data_get($connection->configuration, 'driver', 'generic'),
                'event_key' => 'incoming_message',
                'recipient_address' => $from,
                'content' => mb_substr($message, 0, 10000),
                'status' => 'received',
                'provider_message_id' => $providerMessageId,
                'delivered_at' => now(),
                'metadata' => [
                    'timestamp' => $this->scalar(Arr::get($payload, 'timestamp')),
                    'device' => $this->scalar(Arr::get($payload, 'device')),
                ],
            ],
        );

        $connection->update(['last_checked_at' => now(), 'last_error' => null]);

        return response()->json(['ok' => true, 'kind' => 'incoming_message', 'duplicate' => ! $delivery->wasRecentlyCreated]);
    }

    private function scalar(mixed $value): ?string
    {
        return is_scalar($value) ? trim((string) $value) : null;
    }
}
