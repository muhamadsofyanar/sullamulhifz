<?php

namespace App\Services;

/** @phase 5.2 Smart Assistant with human review */

use App\Models\MentorshipSession;
use App\Models\PersonalGoal;
use App\Models\PersonalModuleEnrollment;
use App\Models\PersonalPracticeEntry;
use App\Models\User;

class PersonalSmartAssistantService
{
    public function snapshot(User $user): array
    {
        $profile = $user->personalProfile()->firstOrFail();

        $goals = PersonalGoal::query()
            ->where('user_id', $user->id)
            ->where('personal_profile_id', $profile->id)
            ->where('status', 'active')
            ->orderByRaw('due_on is null, due_on asc')
            ->limit(5)
            ->get();

        $practice = PersonalPracticeEntry::query()
            ->where('user_id', $user->id)
            ->where('personal_profile_id', $profile->id)
            ->where('practiced_on', '>=', today()->subDays(29))
            ->get();

        $modules = PersonalModuleEnrollment::query()
            ->where('user_id', $user->id)
            ->where('personal_profile_id', $profile->id)
            ->where('status', 'active')
            ->pluck('module_key')
            ->values();

        $sessions = MentorshipSession::query()
            ->where('learner_user_id', $user->id)
            ->whereIn('status', ['requested', 'scheduled', 'completed'])
            ->latest('id')
            ->limit(5)
            ->get();

        $practiceMinutes = (int) $practice->sum('duration_minutes');
        $practiceSessions = $practice->count();
        $daysPracticed = $practice->pluck('practiced_on')->filter()->map(fn ($date) => $date->toDateString())->unique()->count();

        $recommendations = [];
        if ($practiceSessions === 0) {
            $recommendations[] = $this->recommendation('Mulai dari satu sesi kecil', 'Belum ada latihan 30 hari terakhir. Mulai 5–10 menit agar ritme terbentuk.', 'practice');
        } elseif ($daysPracticed < 3) {
            $recommendations[] = $this->recommendation('Bangun ritme, bukan mengejar durasi', "Latihan tercatat pada {$daysPracticed} hari dalam 30 hari. Tambahkan hari latihan pendek dan konsisten.", 'practice');
        } else {
            $recommendations[] = $this->recommendation('Pertahankan ritme belajar', "Ada {$practiceSessions} sesi dengan total {$practiceMinutes} menit dalam 30 hari.", 'practice');
        }

        $nearestGoal = $goals->first();
        if ($nearestGoal) {
            $progress = (float) ($nearestGoal->progress_value ?? 0);
            $target = max(1, (float) ($nearestGoal->target_value ?? 1));
            $percent = min(100, (int) round(($progress / $target) * 100));
            $recommendations[] = $this->recommendation('Fokus target aktif', "{$nearestGoal->title} saat ini sekitar {$percent}% dari target yang dicatat.", 'goal');
        } else {
            $recommendations[] = $this->recommendation('Tetapkan satu target sederhana', 'Target yang jelas membantu Ruang Belajar menentukan langkah berikutnya.', 'goal');
        }

        if ($sessions->whereIn('status', ['requested', 'scheduled'])->isNotEmpty()) {
            $recommendations[] = $this->recommendation('Siapkan sesi bersama Ustadz', 'Ada sesi bimbingan yang diminta atau terjadwal. Catat satu fokus yang ingin dibahas.', 'mentor');
        }

        if ($modules->contains('academy')) {
            $recommendations[] = $this->recommendation('Hubungkan Academy dengan target', 'Academy aktif. Pilih materi yang membantu target Qur’ani yang sedang dikerjakan.', 'academy');
        }

        return [
            'profile' => $profile,
            'goals' => $goals,
            'modules' => $modules,
            'practice_sessions' => $practiceSessions,
            'practice_minutes' => $practiceMinutes,
            'days_practiced' => $daysPracticed,
            'mentor_sessions' => $sessions,
            'recommendations' => $recommendations,
            'privacy' => 'Analisis ini dibuat dari data akun sendiri di server Sullamul Hifz. Tidak ada jurnal privat yang dikirim ke provider AI eksternal.',
            'engine' => 'sullam-local-guidance-v1',
        ];
    }

    public function draftText(array $snapshot): string
    {
        $lines = ['Rangkuman pendampingan belajar:'];
        foreach ($snapshot['recommendations'] as $recommendation) {
            $lines[] = '- '.$recommendation['title'].': '.$recommendation['body'];
        }
        $lines[] = '';
        $lines[] = 'Draft ini perlu ditinjau Ustadz sebelum dianggap sebagai arahan manusia.';

        return implode("\n", $lines);
    }

    private function recommendation(string $title, string $body, string $type): array
    {
        return compact('title', 'body', 'type');
    }
}
