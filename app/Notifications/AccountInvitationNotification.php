<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountInvitationNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly string $activationUrl)
    {
    }

    public function via(object $notifiable): array
    {
        return $notifiable->email ? ['mail'] : [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Aktivasi akun Sullamul Ḥifẓ')
            ->greeting('Assalamu’alaikum, '.$notifiable->name)
            ->line('Akun Anda telah disiapkan oleh pengelola lembaga.')
            ->action('Aktifkan akun', $this->activationUrl)
            ->line('Tautan berlaku selama 48 jam. Abaikan pesan ini apabila Anda tidak mengenali undangan tersebut.');
    }
}
