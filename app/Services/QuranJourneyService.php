<?php

namespace App\Services;

use App\Models\MarhalahType;
use App\Models\MemorizationMilestone;
use App\Models\MemorizationRetentionCheck;
use App\Models\QuranAyah;
use App\Models\QuranDivisionUnit;
use App\Models\QuranJourneyProfile;
use App\Models\QuranJourneyPortion;
use App\Models\QuranSurah;
use App\Models\MemorizationTarget;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\StudentMarhalahHistory;
use App\Models\Teacher;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuranJourneyService
{
    /** @return array<int,array<string,mixed>> */
    public function stageRules(): array
    {
        return [
            30 => ['code'=>'ayah','name'=>'Āyah','portion'=>'1 ayat atau lebih','unit'=>'ayah','value'=>1,'stage'=>'fondasi_30'],
            29 => ['code'=>'tsalatsiyyah','name'=>'Tsalātsiyyah','portion'=>'3 baris','unit'=>'line','value'=>3,'stage'=>'fondasi_29'],
            28 => ['code'=>'khamsiyyah','name'=>'Khamsiyyah','portion'=>'5 baris','unit'=>'line','value'=>5,'stage'=>'fondasi_28'],
            27 => ['code'=>'nisfiyyah','name'=>'Niṣfiyyah','portion'=>'½ halaman','unit'=>'page','value'=>0.5,'stage'=>'fondasi_27'],
            26 => ['code'=>'safhah','name'=>'Ṣafḥah','portion'=>'1 halaman','unit'=>'page','value'=>1,'stage'=>'fondasi_26'],
            1 => ['code'=>'safhatayn','name'=>'Ṣafḥatayn','portion'=>'2 halaman','unit'=>'page','value'=>2,'stage'=>'utama_1_25'],
        ];
    }

    /** @return array<string,mixed> */
    public function ruleForJuz(int $juz): array
    {
        if ($juz >= 1 && $juz <= 25) {
            return $this->stageRules()[1] + ['juz'=>$juz];
        }
        if (isset($this->stageRules()[$juz])) {
            return $this->stageRules()[$juz] + ['juz'=>$juz];
        }
        throw ValidationException::withMessages(['juz' => 'Juz perjalanan harus berada antara 1 sampai 30.']);
    }

    public function marhalahForJuz(int $juz): ?MarhalahType
    {
        $rule = $this->ruleForJuz($juz);
        return MarhalahType::query()->where('code', $rule['code'])->where('status', 'active')->first();
    }

    public function initializeProfile(Student $student, Teacher $teacher, int $juz, string $cadenceMode = 'flexible', ?string $cadenceNotes = null, ?string $reason = null): QuranJourneyProfile
    {
        $rule = $this->ruleForJuz($juz);
        $marhalah = $this->marhalahForJuz($juz);
        if (! $marhalah) {
            throw ValidationException::withMessages(['juz' => 'Master Marhalah belum siap untuk Juz '.$juz.'.']);
        }

        return DB::transaction(function () use ($student, $teacher, $juz, $cadenceMode, $cadenceNotes, $reason, $rule, $marhalah): QuranJourneyProfile {
            $profile = QuranJourneyProfile::query()->firstOrCreate(
                ['institution_id'=>$student->institution_id,'student_id'=>$student->id],
                [
                    'current_marhalah_type_id'=>$marhalah->id,
                    'current_juz_number'=>$juz,
                    'stage_code'=>$rule['stage'],
                    'cadence_mode'=>$cadenceMode,
                    'cadence_notes'=>$cadenceNotes,
                    'started_at'=>now(),
                    'updated_by_teacher_id'=>$teacher->id,
                    'status'=>'active',
                ],
            );

            if (! $profile->wasRecentlyCreated) {
                throw ValidationException::withMessages(['juz' => 'Perjalanan Qur’an santri sudah diinisialisasi. Perubahan tahap berikutnya dilakukan melalui alur lanjut Juz.']);
            }

            StudentMarhalahHistory::query()->create([
                'student_id'=>$student->id,
                'marhalah_type_id'=>$marhalah->id,
                'effective_from'=>today(),
                'decision'=>'initial_position',
                'reason'=>$reason ?: 'Posisi awal perjalanan Qur’an ditetapkan guru saat migrasi ke Qur’an Journey.',
                'decided_by_teacher_id'=>$teacher->id,
                'evidence_notes'=>'Juz '.$juz.' · '.$rule['name'].' · porsi '.$rule['portion'].'.',
                'status'=>'active',
            ]);

            $this->ensureJuzMilestone($student, $juz);
            return $profile->fresh('marhalah');
        });
    }

    public function nextJuz(?int $current): ?int
    {
        if ($current === null) return 30;
        return match (true) {
            $current === 30 => 29,
            $current === 29 => 28,
            $current === 28 => 27,
            $current === 27 => 26,
            $current === 26 => 1,
            $current >= 1 && $current < 25 => $current + 1,
            $current === 25 => null,
            default => null,
        };
    }

    public function advance(Student $student, Teacher $teacher): QuranJourneyProfile
    {
        $profile = QuranJourneyProfile::query()
            ->where('institution_id', $student->institution_id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $currentJuz = (int) $profile->current_juz_number;
        $currentMilestone = $this->ensureJuzMilestone($student, $currentJuz);
        if ($currentMilestone->memorization_status !== 'completed') {
            throw ValidationException::withMessages(['stage' => 'Juz '.$currentJuz.' belum ditandai selesai hafalan. Konfirmasi milestone Juz terlebih dahulu.']);
        }

        $next = $this->nextJuz($currentJuz);
        if ($next === null) {
            $profile->update(['status'=>'completed','updated_by_teacher_id'=>$teacher->id]);
            $this->refreshAggregateMilestones($student, $teacher);
            return $profile->fresh('marhalah');
        }

        $rule = $this->ruleForJuz($next);
        $marhalah = $this->marhalahForJuz($next);
        if (! $marhalah) {
            throw ValidationException::withMessages(['stage' => 'Master Marhalah untuk tahap berikutnya belum tersedia.']);
        }

        DB::transaction(function () use ($student, $teacher, $profile, $currentJuz, $next, $rule, $marhalah): void {
            StudentMarhalahHistory::query()
                ->where('student_id', $student->id)
                ->where('status', 'active')
                ->update(['status'=>'completed','effective_until'=>today()]);

            StudentMarhalahHistory::query()->create([
                'student_id'=>$student->id,
                'marhalah_type_id'=>$marhalah->id,
                'effective_from'=>today(),
                'decision'=>'advance_by_juz',
                'reason'=>'Juz '.$currentJuz.' telah selesai dan guru mengonfirmasi lanjut ke Juz '.$next.'.',
                'decided_by_teacher_id'=>$teacher->id,
                'evidence_notes'=>$rule['name'].' · standar porsi '.$rule['portion'].'.',
                'status'=>'active',
            ]);

            $profile->update([
                'current_juz_number'=>$next,
                'current_marhalah_type_id'=>$marhalah->id,
                'stage_code'=>$rule['stage'],
                'updated_by_teacher_id'=>$teacher->id,
                'foundation_completed_at'=>$currentJuz === 26 ? ($profile->foundation_completed_at ?: now()) : $profile->foundation_completed_at,
                'status'=>'active',
            ]);

            $this->ensureJuzMilestone($student, $next);
            $this->refreshAggregateMilestones($student, $teacher);
        });

        return $profile->fresh('marhalah');
    }

    /** @return array{juz:int,marhalah:?MarhalahType,rule:array<string,mixed>} */
    public function resolveRange(Student $student, int $surahId, int $startVerse, int $endVerse, bool $enforceProfile = true): array
    {
        $start = QuranAyah::query()->where('surah_id', $surahId)->where('verse_number', $startVerse)->first();
        $end = QuranAyah::query()->where('surah_id', $surahId)->where('verse_number', $endVerse)->first();
        if (! $start || ! $end || ! $start->juz_number || ! $end->juz_number) {
            throw ValidationException::withMessages(['surah_id' => 'Metadata Juz untuk rentang ayat belum tersedia. Pastikan Full Qur’an Engine sudah tersinkron.']);
        }
        if ((int) $start->juz_number !== (int) $end->juz_number) {
            throw ValidationException::withMessages(['end_verse' => 'Satu porsi hafalan baru tidak boleh melintasi batas Juz. Buat target terpisah.']);
        }

        $juz = (int) $start->juz_number;
        $profile = QuranJourneyProfile::query()->where('student_id', $student->id)->where('institution_id', $student->institution_id)->first();
        if ($enforceProfile && $profile && (int) $profile->current_juz_number !== $juz) {
            throw ValidationException::withMessages(['surah_id' => 'Santri sedang berada pada Juz '.$profile->current_juz_number.'. Target hafalan baru harus mengikuti Juz perjalanan aktif.']);
        }

        return ['juz'=>$juz,'marhalah'=>$this->marhalahForJuz($juz),'rule'=>$this->ruleForJuz($juz)];
    }

    public function ensureJuzMilestone(Student $student, int $juz): MemorizationMilestone
    {
        $division = QuranDivisionUnit::query()->where('unit_type', 'juz')->where('unit_number', $juz)->first();
        return MemorizationMilestone::query()->firstOrCreate(
            [
                'institution_id'=>$student->institution_id,
                'student_id'=>$student->id,
                'unit_type'=>'juz',
                'unit_key'=>(string) $juz,
            ],
            [
                'label'=>'Juz '.$juz,
                'start_surah_id'=>$division?->start_surah_id,
                'end_surah_id'=>$division?->end_surah_id,
                'start_global_number'=>$division?->start_global_number,
                'end_global_number'=>$division?->end_global_number,
                'memorization_status'=>'in_progress',
                'retention_status'=>'not_assessed',
            ],
        );
    }

    public function updateMilestone(Student $student, Teacher $teacher, string $unitType, string $unitKey, ?string $label, string $memorizationStatus, ?string $notes = null): MemorizationMilestone
    {
        $allowedTypes = ['surah','rubu','hizb','juz','fami_manzil','foundation_five','full_quran'];
        if (! in_array($unitType, $allowedTypes, true)) {
            throw ValidationException::withMessages(['unit_type'=>'Jenis milestone tidak dikenal.']);
        }
        if (! in_array($memorizationStatus, ['not_started','in_progress','completed'], true)) {
            throw ValidationException::withMessages(['memorization_status'=>'Status hafalan milestone tidak dikenal.']);
        }

        $division = null;
        if (in_array($unitType, ['rubu','hizb','juz','fami_manzil'], true) && ctype_digit($unitKey)) {
            $division = QuranDivisionUnit::query()->where('unit_type',$unitType)->where('unit_number',(int)$unitKey)->first();
        }
        if ($unitType === 'surah' && ctype_digit($unitKey)) {
            $first = QuranAyah::query()->where('surah_id',(int)$unitKey)->orderBy('global_number')->first();
            $last = QuranAyah::query()->where('surah_id',(int)$unitKey)->orderByDesc('global_number')->first();
            $bounds = $first && $last ? [$first,$last] : null;
            $surah = QuranSurah::query()->find((int)$unitKey);
        } else {
            $bounds = null;
            $surah = null;
        }
        $resolvedLabel = $division?->label ?? $surah?->name_latin ?? trim((string)$label);
        if ($resolvedLabel === '') {
            throw ValidationException::withMessages(['unit_key'=>'Nomor/kunci milestone tidak ditemukan pada Peta Mushaf.']);
        }

        $startBound = $bounds[0] ?? null;
        $endBound = $bounds[1] ?? null;
        $milestone = MemorizationMilestone::query()->firstOrNew(
            ['institution_id'=>$student->institution_id,'student_id'=>$student->id,'unit_type'=>$unitType,'unit_key'=>$unitKey],
        );
        $wasCompleted = $milestone->memorization_status === 'completed';
        $milestone->fill([
            'label'=>$resolvedLabel,
            'start_surah_id'=>$division?->start_surah_id ?? $startBound?->surah_id,
            'end_surah_id'=>$division?->end_surah_id ?? $endBound?->surah_id,
            'start_global_number'=>$division?->start_global_number ?? $startBound?->global_number,
            'end_global_number'=>$division?->end_global_number ?? $endBound?->global_number,
            'memorization_status'=>$memorizationStatus,
            'memorized_at'=>$memorizationStatus === 'completed' ? ($wasCompleted ? $milestone->memorized_at : now()) : null,
            'verified_by_teacher_id'=>$teacher->id,
            'notes'=>$notes,
        ]);
        $milestone->save();

        $this->refreshAggregateMilestones($student, $teacher);
        return $milestone;
    }

    public function recordRetention(MemorizationMilestone $milestone, Student $student, Teacher $teacher, string $result, string $assistanceLevel, ?string $notes = null, ?string $nextCheckDate = null): MemorizationRetentionCheck
    {
        if ((int) $milestone->student_id !== (int) $student->id || (int) $milestone->institution_id !== (int) $student->institution_id) {
            throw ValidationException::withMessages(['milestone'=>'Milestone tidak termasuk perjalanan santri ini.']);
        }
        $retention = match ($result) {
            'maintained' => 'maintained',
            'strengthening_needed' => 'strengthening',
            'reactivation_needed' => 'reactivation',
            default => throw ValidationException::withMessages(['result'=>'Hasil pemeriksaan penjagaan tidak dikenal.']),
        };

        $check = MemorizationRetentionCheck::query()->create([
            'institution_id'=>$student->institution_id,
            'memorization_milestone_id'=>$milestone->id,
            'student_id'=>$student->id,
            'teacher_id'=>$teacher->id,
            'result'=>$result,
            'assistance_level'=>$assistanceLevel,
            'checked_at'=>now(),
            'next_check_date'=>$nextCheckDate,
            'notes'=>$notes,
        ]);

        $milestone->update([
            'retention_status'=>$retention,
            'maintained_at'=>$retention === 'maintained' ? now() : null,
            'verified_by_teacher_id'=>$teacher->id,
        ]);
        return $check;
    }

    public function createPortion(
        Student $student,
        Teacher $teacher,
        int $startSurahId,
        int $startVerse,
        int $endSurahId,
        int $endVerse,
        bool $teacherConfirmed,
        ?string $scheduledFor = null,
        ?string $dueDate = null,
        ?string $notes = null,
    ): QuranJourneyPortion {
        if (! $teacherConfirmed) {
            throw ValidationException::withMessages(['teacher_confirmed'=>'Guru perlu mengonfirmasi porsi sesuai Marhalah pada Mushaf Madinah. Sistem tidak menebak jumlah baris secara otomatis.']);
        }
        $profile = QuranJourneyProfile::query()
            ->where('institution_id',$student->institution_id)->where('student_id',$student->id)->firstOrFail();
        $startAyah = QuranAyah::query()->where('surah_id',$startSurahId)->where('verse_number',$startVerse)->first();
        $endAyah = QuranAyah::query()->where('surah_id',$endSurahId)->where('verse_number',$endVerse)->first();
        if (! $startAyah || ! $endAyah || ! $startAyah->juz_number || ! $endAyah->juz_number) {
            throw ValidationException::withMessages(['start_surah_id'=>'Rentang porsi tidak ditemukan pada korpus Al-Qur’an.']);
        }
        if ((int)$startAyah->global_number > (int)$endAyah->global_number) {
            throw ValidationException::withMessages(['end_verse'=>'Akhir porsi harus berada setelah awal porsi.']);
        }
        if ((int)$startAyah->juz_number !== (int)$endAyah->juz_number) {
            throw ValidationException::withMessages(['end_surah_id'=>'Satu porsi Marhalah tidak melintasi batas Juz. Pisahkan di batas Juz.']);
        }
        $juz = (int)$startAyah->juz_number;
        if ((int)$profile->current_juz_number !== $juz) {
            throw ValidationException::withMessages(['start_surah_id'=>'Santri sedang berada pada Juz '.$profile->current_juz_number.'. Porsi baru harus berada pada Juz aktif.']);
        }
        $rule = $this->ruleForJuz($juz);
        $marhalah = $this->marhalahForJuz($juz);
        if (! $marhalah) {
            throw ValidationException::withMessages(['start_surah_id'=>'Master Marhalah untuk Juz ini belum tersedia.']);
        }
        $activeYear = AcademicYear::query()->where('institution_id',$student->institution_id)->where('is_active',true)->first();
        if (! $activeYear) {
            throw ValidationException::withMessages(['start_surah_id'=>'Tahun ajaran aktif belum tersedia untuk membuat target setoran.']);
        }

        return DB::transaction(function () use ($student,$teacher,$startAyah,$endAyah,$juz,$rule,$marhalah,$activeYear,$scheduledFor,$dueDate,$notes): QuranJourneyPortion {
            $portion = QuranJourneyPortion::query()->create([
                'institution_id'=>$student->institution_id,
                'student_id'=>$student->id,
                'marhalah_type_id'=>$marhalah->id,
                'assigned_by_teacher_id'=>$teacher->id,
                'journey_juz_number'=>$juz,
                'portion_unit'=>$rule['unit'],
                'portion_value'=>$rule['value'],
                'portion_label'=>$rule['portion'],
                'start_global_number'=>$startAyah->global_number,
                'end_global_number'=>$endAyah->global_number,
                'start_surah_id'=>$startAyah->surah_id,
                'start_verse'=>$startAyah->verse_number,
                'end_surah_id'=>$endAyah->surah_id,
                'end_verse'=>$endAyah->verse_number,
                'start_page_number'=>$startAyah->page_number,
                'end_page_number'=>$endAyah->page_number,
                'teacher_confirmed'=>true,
                'status'=>'planned',
                'scheduled_for'=>$scheduledFor,
                'due_date'=>$dueDate,
                'notes'=>$notes,
            ]);

            $ayahs = QuranAyah::query()->whereBetween('global_number',[$startAyah->global_number,$endAyah->global_number])
                ->orderBy('global_number')->get()->groupBy('surah_id');
            foreach ($ayahs as $surahId => $surahAyahs) {
                MemorizationTarget::query()->create([
                    'institution_id'=>$student->institution_id,
                    'academic_year_id'=>$activeYear->id,
                    'student_id'=>$student->id,
                    'assigned_by_teacher_id'=>$teacher->id,
                    'quran_journey_portion_id'=>$portion->id,
                    'surah_id'=>(int)$surahId,
                    'start_verse'=>(int)$surahAyahs->first()->verse_number,
                    'end_verse'=>(int)$surahAyahs->last()->verse_number,
                    'marhalah_type_id'=>$marhalah->id,
                    'journey_juz_number'=>$juz,
                    'portion_confirmed'=>true,
                    'portion_note'=>'Bagian dari porsi Marhalah #'.$portion->id.' · '.$rule['portion'].'.',
                    'target_type'=>'new_memorization',
                    'status'=>'active',
                    'target_date'=>$scheduledFor,
                    'due_date'=>$dueDate,
                    'notes'=>$notes,
                ]);
            }
            return $portion->fresh(['marhalah','startSurah','endSurah','targets.surah']);
        });
    }

    public function refreshPortionForTarget(MemorizationTarget $target): void
    {
        if (! $target->quran_journey_portion_id) return;
        $portion = QuranJourneyPortion::query()->with('targets')->find($target->quran_journey_portion_id);
        if (! $portion || $portion->targets->isEmpty()) return;
        $statuses = $portion->targets->pluck('status');
        $status = match (true) {
            $statuses->every(fn($value)=>$value === 'completed') => 'completed',
            $statuses->contains('strengthening') => 'strengthening',
            $statuses->contains('paused') => 'paused',
            $statuses->contains('in_progress') || $statuses->contains('completed') => 'in_progress',
            default => 'planned',
        };
        $portion->update([
            'status'=>$status,
            'completed_at'=>$status === 'completed' ? ($portion->completed_at ?: now()) : null,
        ]);
    }

    public function refreshAggregateMilestones(Student $student, ?Teacher $teacher = null): void
    {
        $completedJuz = MemorizationMilestone::query()
            ->where('student_id',$student->id)->where('institution_id',$student->institution_id)
            ->where('unit_type','juz')->where('memorization_status','completed')->pluck('unit_key')->map(fn($v)=>(int)$v);

        $foundationComplete = collect([26,27,28,29,30])->every(fn(int $juz): bool => $completedJuz->contains($juz));
        if ($foundationComplete) {
            $this->completeAggregateMilestone($student,$teacher,'foundation_five','juz26-30','Fondasi 5 Juz (Juz 26–30)',
                'Tahap fondasi selesai. Bagian Qāf–An-Nās di dalamnya mencakup manzil Qaf pada pola Fami Bisyauqin; status penjagaan tetap dinilai terpisah.');
            $this->completeAggregateMilestone($student,$teacher,'fami_manzil','7','Manzil Qaf — Qāf sampai An-Nās',
                'Hafalan wilayah manzil Qaf tercakup dalam Fondasi 5 Juz; belum otomatis berarti terjaga.');
        }

        $allComplete = collect(range(1,30))->every(fn(int $juz): bool => $completedJuz->contains($juz));
        if ($allComplete) {
            $this->completeAggregateMilestone($student,$teacher,'full_quran','30-juz','Hafalan 30 Juz selesai',
                'Selesai hafalan tidak otomatis berarti seluruh hafalan terjaga. Penjagaan dinilai melalui milestone dan pemeriksaan retention.');
        }
    }

    private function completeAggregateMilestone(Student $student, ?Teacher $teacher, string $unitType, string $unitKey, string $label, string $notes): void
    {
        $milestone = MemorizationMilestone::query()->firstOrNew([
            'institution_id'=>$student->institution_id,'student_id'=>$student->id,'unit_type'=>$unitType,'unit_key'=>$unitKey,
        ]);
        $milestone->fill([
            'label'=>$label,'memorization_status'=>'completed','verified_by_teacher_id'=>$teacher?->id,'notes'=>$notes,
        ]);
        $milestone->memorized_at ??= now();
        $milestone->save();
    }

    /** @return array<string,mixed> */
    public function summary(Student $student): array
    {
        $profile = QuranJourneyProfile::query()->with('marhalah')->where('student_id',$student->id)->where('institution_id',$student->institution_id)->first();
        $currentJuz = $profile?->current_juz_number;
        $currentMilestone = $currentJuz ? MemorizationMilestone::query()->where('student_id',$student->id)->where('unit_type','juz')->where('unit_key',(string)$currentJuz)->first() : null;
        $completedJuz = MemorizationMilestone::query()->where('student_id',$student->id)->where('unit_type','juz')->where('memorization_status','completed')->count();
        $foundationCompleted = MemorizationMilestone::query()->where('student_id',$student->id)->where('unit_type','foundation_five')->where('memorization_status','completed')->exists();
        $qaf = MemorizationMilestone::query()->where('student_id',$student->id)->where('unit_type','fami_manzil')->where('unit_key','7')->first();

        return [
            'profile'=>$profile,
            'rule'=>$currentJuz ? $this->ruleForJuz((int)$currentJuz) : null,
            'currentMilestone'=>$currentMilestone,
            'nextJuz'=>$profile ? $this->nextJuz((int)$currentJuz) : null,
            'completedJuz'=>$completedJuz,
            'foundationCompleted'=>$foundationCompleted,
            'qafMilestone'=>$qaf,
        ];
    }
}
