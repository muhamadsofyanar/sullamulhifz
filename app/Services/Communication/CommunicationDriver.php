<?php

namespace App\Services\Communication;

use App\Models\CommunicationDelivery;

interface CommunicationDriver
{
    /** @return array{provider_message_id:?string,metadata?:array<string,mixed>} */
    public function send(CommunicationDelivery $delivery): array;

    public function ready(): bool;

    public function readinessMessage(): string;
}
