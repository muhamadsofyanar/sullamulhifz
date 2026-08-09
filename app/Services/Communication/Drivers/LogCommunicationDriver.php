<?php

namespace App\Services\Communication\Drivers;

use App\Models\CommunicationDelivery;
use App\Services\Communication\CommunicationDriver;
use Illuminate\Support\Facades\Log;

class LogCommunicationDriver implements CommunicationDriver
{
    public function __construct(private readonly string $channel)
    {
    }

    public function send(CommunicationDelivery $delivery): array
    {
        Log::info('Simulasi pengiriman komunikasi', [
            'delivery_id' => $delivery->id,
            'institution_id' => $delivery->institution_id,
            'channel' => $this->channel,
            'event_key' => $delivery->event_key,
            'recipient' => $this->mask($delivery->recipient_address),
        ]);

        return ['provider_message_id' => 'log-'.$delivery->id, 'metadata' => ['simulated' => true]];
    }

    public function ready(): bool
    {
        return true;
    }

    public function readinessMessage(): string
    {
        return 'Mode simulasi aktif; pesan hanya dicatat ke log dan tidak dikirim keluar.';
    }

    private function mask(string $address): string
    {
        if (str_contains($address, '@')) {
            [$local, $domain] = explode('@', $address, 2);

            return mb_substr($local, 0, 2).'***@'.$domain;
        }

        return mb_substr($address, 0, 4).'***'.mb_substr($address, -2);
    }
}
