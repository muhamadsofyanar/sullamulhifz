<?php

namespace App\Services\Communication\Drivers;

use App\Models\CommunicationDelivery;
use App\Services\Communication\CommunicationDriver;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use RuntimeException;

class SmtpEmailDriver implements CommunicationDriver
{
    public function send(CommunicationDelivery $delivery): array
    {
        if (! $this->ready()) {
            throw new RuntimeException($this->readinessMessage());
        }

        Mail::mailer('smtp')->html($delivery->content, function (Message $message) use ($delivery): void {
            $message->to($delivery->recipient_address, $delivery->recipient_name)
                ->subject($delivery->subject ?: config('app.name'));

            if (filled(config('communications.email.reply_to'))) {
                $message->replyTo((string) config('communications.email.reply_to'));
            }
        });

        return ['provider_message_id' => null, 'metadata' => ['transport' => 'smtp']];
    }

    public function ready(): bool
    {
        $host = strtolower((string) config('mail.mailers.smtp.host'));
        $from = (string) config('mail.from.address');

        return $host !== ''
            && ! in_array($host, ['127.0.0.1', 'localhost'], true)
            && filter_var($from, FILTER_VALIDATE_EMAIL) !== false
            && $from !== 'hello@example.com';
    }

    public function readinessMessage(): string
    {
        return $this->ready()
            ? 'Konfigurasi SMTP dasar tersedia.'
            : 'MAIL_HOST dan MAIL_FROM_ADDRESS yang valid wajib diisi di Coolify.';
    }
}
