<?php

namespace App\Notifications;

use App\Models\InfaqRealisation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InfaqRealisationStatusNotification extends Notification
{
    use Queueable;
    public function __construct(private readonly InfaqRealisation $realisation) {}
    public function via(object $notifiable): array { return $notifiable->email ? ['database', 'mail'] : ['database']; }
    public function toArray(object $notifiable): array
    {
        return ['kind' => 'infaq_realisation_status', 'realisation_id' => $this->realisation->public_id, 'status' => $this->realisation->status, 'title' => 'Pembaruan realisasi '.$this->realisation->program_name, 'body' => $this->realisation->status === 'verified' ? 'Realisasi telah diverifikasi dan masuk laporan dampak.' : ($this->realisation->review_note ?: 'Realisasi perlu diperbaiki.')];
    }
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)->subject('Pembaruan realisasi infak')->greeting('Assalamu’alaikum, '.$notifiable->name)->line($this->toArray($notifiable)['body'])->action('Buka tata kelola infak', route('admin.infaq.realisations.index'));
    }
}
