<?php

namespace App\Notifications;

use App\Models\MemorizationReviewPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MurajaahDueNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly MemorizationReviewPlan $plan)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => 'murajaah_due',
            'review_plan_id' => $this->plan->id,
            'student_id' => $this->plan->student_id,
            'title' => 'Waktunya Murāja‘ah',
            'body' => 'Jadwal penjagaan Al-Qur’an sudah mendekat. Buka perjalanan Tahfizh untuk melihat bagian yang perlu dijaga.',
            'review_date' => $this->plan->review_date?->toDateString(),
        ];
    }
}
