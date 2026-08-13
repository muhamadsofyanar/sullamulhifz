<?php

namespace App\Notifications;

use App\Models\InfaqTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InfaqStatusNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly InfaqTransaction $transaction) {}

    public function via(object $notifiable): array
    {
        return $notifiable->email ? ['database', 'mail'] : ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'infaq_status', 'transaction_id' => $this->transaction->public_id,
            'status' => $this->transaction->status,
            'title' => $this->transaction->status === 'verified' ? 'Infak telah terverifikasi' : 'Infak memerlukan perhatian',
            'body' => $this->transaction->status === 'verified'
                ? 'Pencocokan mutasi selesai dan bukti penerimaan sudah tersedia.'
                : ($this->transaction->rejection_reason ?: 'Silakan periksa kembali informasi transfer.'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->transaction->status === 'verified' ? 'Infak terverifikasi' : 'Pembaruan status infak')
            ->greeting('Assalamu’alaikum, '.$notifiable->name)
            ->line($this->toArray($notifiable)['body'])
            ->action('Lihat riwayat infak', route('infaq.index'));
    }
}
