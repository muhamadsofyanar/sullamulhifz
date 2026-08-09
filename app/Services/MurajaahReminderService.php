<?php

namespace App\Services;

use App\Models\MemorizationReviewPlan;
use App\Models\PersonalProfile;
use App\Notifications\MurajaahDueNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class MurajaahReminderService
{
    /** @return array{plans:int,recipients:int} */
    public function sendDue(?Carbon $through = null): array
    {
        $through ??= today()->addDay();
        $plans = 0;
        $recipients = 0;

        MemorizationReviewPlan::query()
            ->with(['student.guardians.user'])
            ->where('status', 'scheduled')
            ->whereDate('review_date', '<=', $through->toDateString())
            ->whereNull('reminder_sent_at')
            ->orderBy('id')
            ->chunkById(100, function ($items) use (&$plans, &$recipients): void {
                foreach ($items as $plan) {
                    $users = $this->recipients($plan);
                    if ($users->isEmpty()) {
                        continue;
                    }

                    foreach ($users as $user) {
                        $user->notify(new MurajaahDueNotification($plan));
                        $recipients++;
                    }

                    $plan->forceFill(['reminder_sent_at' => now()])->save();
                    $plans++;
                }
            });

        return ['plans' => $plans, 'recipients' => $recipients];
    }

    private function recipients(MemorizationReviewPlan $plan): Collection
    {
        $guardianUsers = $plan->student->guardians
            ->filter(fn ($guardian): bool => (bool) $guardian->pivot?->can_receive_notifications)
            ->pluck('user')
            ->filter();

        $personalUser = PersonalProfile::query()
            ->with('user')
            ->where('student_id', $plan->student_id)
            ->first()?->user;

        return $guardianUsers
            ->when($personalUser, fn (Collection $users) => $users->push($personalUser))
            ->unique('id')
            ->values();
    }
}
