<?php

namespace App\Services;

/** @phase 4.5 Personal 2.0 — aspiration-aware guidance without ranking */

use App\Models\PersonalGoal;
use App\Models\PersonalPracticeEntry;
use App\Models\PersonalProfile;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class PersonalJourneyService
{
    public function snapshot(User $user, PersonalProfile $profile): array
    {
        $entries = PersonalPracticeEntry::query()
            ->with('surah')
            ->where('institution_id', $user->institution_id)
            ->where('user_id', $user->id)
            ->latest('practiced_on')->latest('id')
            ->limit(60)->get();

        $week = $entries->filter(fn (PersonalPracticeEntry $entry): bool => $entry->practiced_on->gte(today()->subDays(6)));
        $todayEntries = $entries->filter(fn (PersonalPracticeEntry $entry): bool => $entry->practiced_on->isToday());
        $goals = PersonalGoal::query()
            ->where('institution_id', $user->institution_id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->orderByRaw('due_on IS NULL, due_on')
            ->limit(8)->get();

        foreach ($goals as $goal) $this->refreshGoal($goal);

        return [
            'entries' => $entries->take(12),
            'goals' => $goals,
            'today_minutes' => (int) $todayEntries->sum('duration_minutes'),
            'today_sessions' => $todayEntries->count(),
            'week_minutes' => (int) $week->sum('duration_minutes'),
            'week_active_days' => $week->pluck('practiced_on')->map->toDateString()->unique()->count(),
            'week_memorization_verses' => $this->verseTotal($week->where('activity_type', 'memorization')),
            'week_murajaah_verses' => $this->verseTotal($week->where('activity_type', 'murajaah')),
            'streak' => $this->streak($entries),
            'guidance' => $this->guidance($profile, $todayEntries, $entries),
        ];
    }

    public function refreshActiveGoals(User $user): void
    {
        PersonalGoal::query()
            ->where('institution_id', $user->institution_id)
            ->where('user_id', $user->id)
            ->where('status', 'active')->get()
            ->each(fn (PersonalGoal $goal) => $this->refreshGoal($goal));
    }

    public function refreshGoal(PersonalGoal $goal): PersonalGoal
    {
        $entries = PersonalPracticeEntry::query()
            ->where('institution_id', $goal->institution_id)
            ->where('user_id', $goal->user_id)
            ->whereDate('practiced_on', '>=', $goal->starts_on)
            ->when($goal->due_on, fn ($query) => $query->whereDate('practiced_on', '<=', $goal->due_on))
            ->get();

        $value = match ($goal->metric) {
            'practice_minutes' => (int) $entries->sum('duration_minutes'),
            'sessions' => $entries->count(),
            'active_days' => $entries->pluck('practiced_on')->map->toDateString()->unique()->count(),
            'memorization_verses' => $this->verseTotal($entries->where('activity_type', 'memorization')),
            'murajaah_verses' => $this->verseTotal($entries->where('activity_type', 'murajaah')),
            default => 0,
        };

        $goal->progress_value = min((int) $goal->target_value, $value);
        if ($goal->progress_value >= (int) $goal->target_value) {
            $goal->status = 'completed';
            $goal->completed_at = $goal->completed_at ?: now();
        }
        $goal->save();

        return $goal;
    }

    private function guidance(PersonalProfile $profile, Collection $todayEntries, Collection $entries): array
    {
        $items = [];
        $minutes = (int) $todayEntries->sum('duration_minutes');
        $remaining = max(0, (int) $profile->daily_minutes - $minutes);

        if ($profile->aspiration || $profile->quranic_purpose) {
            $aspiration = $profile->aspiration ? " menuju {$profile->aspiration}" : '';
            $items[] = [
                'title' => 'Hubungkan langkah dengan nilai Qur’ani',
                'body' => "Pilih satu nilai yang ingin dijaga hari ini{$aspiration}. Cita-cita memberi konteks, bukan menentukan nilai atau kemampuan Anda.",
                'type' => 'purpose',
            ];
        }

        if ($remaining > 0) {
            $items[] = [
                'title' => 'Jaga ritme hari ini',
                'body' => "Masih ada {$remaining} menit dari ritme harian yang Anda pilih. Ambil sesi pendek yang realistis.",
                'type' => 'consistency',
            ];
        } else {
            $items[] = [
                'title' => 'Target waktu hari ini tercapai',
                'body' => 'Pertahankan kualitas. Tidak perlu menambah beban hanya demi angka.',
                'type' => 'celebrate',
            ];
        }

        if (! $todayEntries->contains('activity_type', 'murajaah')) {
            $recentMemorization = $entries->firstWhere('activity_type', 'memorization');
            $surah = $recentMemorization?->surah?->name_latin;
            $items[] = [
                'title' => 'Sisakan ruang untuk murāja‘ah',
                'body' => $surah
                    ? "Ulangi bagian terbaru dari {$surah} sebelum menambah hafalan baru."
                    : 'Mulai dari hafalan yang paling mudah goyah, lalu catat hasilnya dengan jujur.',
                'type' => 'murajaah',
            ];
        }

        if ($todayEntries->isNotEmpty() && ! $todayEntries->contains('activity_type', 'reflection')) {
            $items[] = [
                'title' => 'Tutup dengan refleksi singkat',
                'body' => 'Catat satu hal yang terasa kuat dan satu bagian yang perlu diulang besok.',
                'type' => 'reflection',
            ];
        }

        return array_slice($items, 0, 3);
    }

    private function verseTotal(Collection $entries): int
    {
        return (int) $entries->sum(fn (PersonalPracticeEntry $entry): int => $entry->verseCount());
    }

    private function streak(Collection $entries): int
    {
        $days = $entries->pluck('practiced_on')->map(fn (CarbonInterface $date): string => $date->toDateString())->unique();
        if ($days->isEmpty()) return 0;

        $cursor = today();
        if (! $days->contains($cursor->toDateString())) $cursor = today()->subDay();

        $streak = 0;
        while ($days->contains($cursor->toDateString())) {
            $streak++;
            $cursor = $cursor->copy()->subDay();
        }
        return $streak;
    }
}
