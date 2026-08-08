<?php

namespace App\Services;

use App\Models\LearningInsight;
use App\Models\LearningObservation;
use App\Models\MemorizationRecord;
use App\Models\MurajaahRecord;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Collection;

class PersonalLearningRecommendationService
{
    public function generate(Student $student, Teacher $teacher, int $userId): LearningInsight
    {
        $institutionId = (int) $student->institution_id;
        abort_unless($institutionId === (int) $teacher->institution_id, 403);

        $observations = LearningObservation::query()
            ->where('institution_id', $institutionId)
            ->where('student_id', $student->id)
            ->latest('observed_at')->limit(8)->get();

        $memorization = MemorizationRecord::query()
            ->where('institution_id', $institutionId)
            ->where('student_id', $student->id)
            ->latest('recorded_at')->limit(3)->get();

        $murajaah = MurajaahRecord::query()
            ->where('institution_id', $institutionId)
            ->where('student_id', $student->id)
            ->latest('recorded_at')->limit(3)->get();

        abort_if($observations->isEmpty() && $memorization->isEmpty() && $murajaah->isEmpty(), 422,
            'Belum ada evidence belajar. Catat observasi atau progres Tahfizh/Muraja\'ah terlebih dahulu.');

        $summary = $this->recommendation($observations, $memorization, $murajaah);
        $evidence = [
            'observation_ids' => $observations->pluck('id')->values()->all(),
            'memorization_record_ids' => $memorization->pluck('id')->values()->all(),
            'murajaah_record_ids' => $murajaah->pluck('id')->values()->all(),
            'evidence_count' => $observations->count() + $memorization->count() + $murajaah->count(),
            'algorithm' => 'evidence-rules-v1',
        ];

        return LearningInsight::create([
            'institution_id' => $institutionId,
            'student_id' => $student->id,
            'created_by_user_id' => $userId,
            'insight_type' => 'personal_recommendation',
            'title' => 'Rekomendasi belajar adaptif — '.$student->full_name,
            'summary' => $summary,
            'evidence' => $evidence,
            'source' => 'evidence_rules',
            'status' => 'pending_review',
            'generated_at' => now(),
        ]);
    }

    private function recommendation(Collection $observations, Collection $memorization, Collection $murajaah): string
    {
        $parts = [];
        $helpful = $observations->first(fn ($item) => $item->effectiveness === 'helpful')
            ?? $observations->first(fn ($item) => $item->effectiveness === 'partly_helpful');

        if ($helpful) {
            $parts[] = 'Pertahankan pendekatan “'.$helpful->method_name.'” sebagai eksperimen belajar berikutnya karena respons observasi menunjukkan manfaat.';
        } elseif ($observations->isNotEmpty()) {
            $parts[] = 'Gunakan sesi berikutnya untuk menguji satu perubahan kecil pada metode belajar; evidence saat ini belum cukup untuk menetapkan pendekatan yang paling membantu.';
        }

        $latestHifz = $memorization->first();
        if ($latestHifz && in_array($latestHifz->result, ['repeat_needed','postponed'], true)) {
            $parts[] = 'Kecilkan beban hafalan baru sementara dan prioritaskan ketepatan serta pengulangan bagian yang masih memerlukan penguatan.';
        } elseif ($latestHifz) {
            $parts[] = 'Beban hafalan dapat dilanjutkan bertahap sambil mempertahankan kualitas bacaan yang sudah tercatat.';
        }

        $latestMurajaah = $murajaah->first();
        if ($latestMurajaah && in_array($latestMurajaah->result, ['strengthening_needed','reactivation_needed'], true)) {
            $parts[] = 'Prioritaskan Muraja‘ah pada materi yang melemah sebelum menambah beban baru.';
        } elseif ($latestMurajaah) {
            $parts[] = 'Pertahankan jadwal Muraja‘ah yang konsisten dan cek kembali respons pada pertemuan berikutnya.';
        }

        $parts[] = 'Guru wajib meninjau rekomendasi ini dan boleh menerima, mengubah, atau menolaknya sesuai kondisi nyata santri.';

        return implode(' ', $parts);
    }
}
