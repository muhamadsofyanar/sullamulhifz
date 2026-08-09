<?php

namespace App\Services\Communication;

use App\Services\Communication\Drivers\GenericWebhookWhatsappDriver;
use App\Services\Communication\Drivers\LogCommunicationDriver;
use App\Services\Communication\Drivers\MailketingEmailDriver;
use App\Services\Communication\Drivers\SmtpEmailDriver;
use App\Services\Communication\Drivers\StarsenderWhatsappDriver;
use InvalidArgumentException;

class CommunicationDriverFactory
{
    public function make(string $channel, string $driver): CommunicationDriver
    {
        return match ($channel.'.'.$driver) {
            'whatsapp.starsender' => app(StarsenderWhatsappDriver::class),
            'whatsapp.generic' => app(GenericWebhookWhatsappDriver::class),
            'whatsapp.log', 'email.log' => new LogCommunicationDriver($channel),
            'email.smtp' => app(SmtpEmailDriver::class),
            'email.mailketing' => app(MailketingEmailDriver::class),
            default => throw new InvalidArgumentException('Driver komunikasi tidak dikenali.'),
        };
    }

    /** @return array<string,string> */
    public function catalog(string $channel): array
    {
        return match ($channel) {
            'whatsapp' => [
                'starsender' => 'StarSender API',
                'generic' => 'OneSender / API WhatsApp generik',
                'log' => 'Log saja (simulasi)',
            ],
            'email' => [
                'smtp' => 'SMTP (termasuk KIRIM.EMAIL/Mailketing SMTP)',
                'mailketing' => 'Mailketing API',
                'log' => 'Log saja (simulasi)',
            ],
            default => [],
        };
    }
}
