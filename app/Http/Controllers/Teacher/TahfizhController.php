<?php

namespace App\Http\Controllers\Teacher;

/** @phase 6.0 Distraction-free Tahfizh */

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AcademicYear;
use App\Models\ClassEnrollment;
use App\Models\GroupMembership;
use App\Models\MarhalahType;
use App\Models\MemorizationRecord;
use App\Models\MemorizationReviewPlan;
use App\Models\MemorizationTarget;
use App\Models\MurajaahRecord;
use App\Models\QuranLearningErrorItem;
use App\Models\QuranSurah;
use App\Models\Student;
use App\Models\StudentMemorizationAssessment;
use App\Models\StudentMemorizationFocus;
use App\Models\TahfizhLearningCycle;
use App\Models\TeacherAssignment;
use App\Services\TahfizhLearningService;
use App\Services\TahfizhProgressService;
use App\Services\QuranJourneyService;
use App\Services\DistractionFreeSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TahfizhController extends Controller
{
    public function __construct(
        private readonly TahfizhProgressService $progress,
        private readonly TahfizhLearningService $learning,
        private readonly QuranJourneyService $journey,
        private readonly DistractionFreeSubmissionService $quickSubmission,
    ) {
    }

    public function index(Request $request): View
    {
        $students = $this->students($request);
        $dashboard = $this->progress->teacherDashboard($students);

        return view('teacher.tahfizh.index', [
            'students' => $students,
            ...$dashboard,
        ]);
    }

    public function student(Request $request, Student $student): View
    {
        $this->authorizeStudent($request, $student);
        $student->load([
            'currentEnrollment.schoolClass',
            'memorizationTargets' => fn ($q) => $q->with(['surah','marhalah'])->latest(),
            'memorizationRecords' => fn ($q) => $q->with(['surah','target','learningCycle'])->latest('recorded_at')->limit(30),
            'murajaahRecords' => fn ($q) => $q->with(['surah','reviewPlan'])->latest('recorded_at')->limit(30),
        ]);

        return view('teacher.tahfizh.student', [
            'student' => $student,
            'summary' => $this->progress->student($student),
            'cycles' => TahfizhLearningCycle::query()
                ->with(['target.surah'])
                ->where('institution_id', $request->user()->institution_id)
                ->where('student_id', $student->id)
                ->latest()->limit(20)->get(),
            'reviewPlans' => MemorizationReviewPlan::query()
                ->with(['surah','target'])
                ->where('institution_id', $request->user()->institution_id)
                ->where('student_id', $student->id)
                ->latest('review_date')->limit(30)->get(),
            'correctionItems' => QuranLearningErrorItem::query()
                ->where('institution_id', $request->user()->institution_id)
                ->where('student_id', $student->id)
                ->latest()->limit(40)->get(),
            'surahs' => QuranSurah::orderBy('id')->get(),
            'marhalah' => MarhalahType::where('status', 'active')->orderBy('sequence')->get(),
            'activeFocus' => StudentMemorizationFocus::activeFor((int) $request->user()->institution_id, (int) $student->id),
            'assessments' => StudentMemorizationAssessment::query()
                ->where('institution_id', $request->user()->institution_id)
                ->where('student_id', $student->id)
                ->latest('assessed_on')->limit(12)->get(),
        ]);
    }

    public function storeCycle(Request $request): RedirectResponse
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);
        $data = $request->validate([
            'student_id' => ['required','exists:students,id'],
            'memorization_target_id' => ['nullable','exists:memorization_targets,id'],
            'cycle_type' => ['required', Rule::in(['new_memorization','initial_repetition','murajaah','talaqqi','tasmi','exam'])],
            'preparation_method' => ['required', Rule::in(['talaqqi','audio_repetition','reading_repetition','writing','word_arrangement','movement','teach_back','mixed','custom'])],
            'teacher_guidance' => ['nullable','string','max:3000'],
            'guardian_guidance' => ['nullable','string','max:3000'],
        ]);
        $student = Student::where('institution_id', $request->user()->institution_id)->findOrFail($data['student_id']);
        $this->authorizeStudent($request, $student);

        $target = null;
        if (! empty($data['memorization_target_id'])) {
            $target = MemorizationTarget::query()
                ->where('institution_id', $request->user()->institution_id)
                ->where('student_id', $student->id)
                ->findOrFail($data['memorization_target_id']);
        }

        $cycle = $this->learning->resolveCycle(
            (int) $request->user()->institution_id,
            (int) $student->id,
            $teacher,
            $target,
            $data['cycle_type'],
            $data['preparation_method'],
        );
        $cycle->update([
            'cycle_type' => $data['cycle_type'],
            'preparation_method' => $data['preparation_method'],
            'teacher_guidance' => $data['teacher_guidance'] ?? $cycle->teacher_guidance,
            'guardian_guidance' => $data['guardian_guidance'] ?? $cycle->guardian_guidance,
        ]);

        return redirect()->route('teacher.tahfizh.student', $student)->with('success', 'Siklus belajar Tahfizh siap digunakan.');
    }

    public function updateCycle(Request $request, TahfizhLearningCycle $cycle): RedirectResponse
    {
        abort_unless((int) $cycle->institution_id === (int) $request->user()->institution_id, 404);
        $student = Student::findOrFail($cycle->student_id);
        $this->authorizeStudent($request, $student);
        $data = $request->validate([
            'status' => ['required', Rule::in(['preparing','ready','submitted','strengthening','completed','paused','cancelled'])],
            'teacher_guidance' => ['nullable','string','max:3000'],
            'guardian_guidance' => ['nullable','string','max:3000'],
        ]);
        $cycle->update([
            ...$data,
            'ready_at' => in_array($data['status'], ['ready','submitted','strengthening','completed'], true) ? ($cycle->ready_at ?: now()) : $cycle->ready_at,
            'completed_at' => $data['status'] === 'completed' ? now() : null,
        ]);
        return back()->with('success', 'Status siklus belajar diperbarui.');
    }

    public function storeQuickMemorization(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudent($request, $student);
        $data = $this->validateQuickSubmission($request, true);
        $record = $this->quickSubmission->recordMemorization($request->user(), $student, $data);
        if ($record->wasRecentlyCreated) {
            $this->log($request, 'tahfizh.quick_memorization_recorded', $record, [
                'student_id' => $student->id,
                'decision' => $record->daily_decision,
                'input_mode' => 'distraction_free',
            ]);
        }

        return redirect()->route('teacher.tahfizh.student', $student)
            ->with('success', 'Hasil setoran tersimpan. Murāja‘ah berikutnya sudah disiapkan.');
    }

    public function storeQuickMurajaah(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudent($request, $student);
        $data = $this->validateQuickSubmission($request, false);
        $record = $this->quickSubmission->recordMurajaah($request->user(), $student, $data);
        if ($record->wasRecentlyCreated) {
            $this->log($request, 'tahfizh.quick_murajaah_recorded', $record, [
                'student_id' => $student->id,
                'decision' => $record->daily_decision,
                'input_mode' => 'distraction_free',
            ]);
        }

        return redirect()->route('teacher.tahfizh.student', $student)
            ->with('success', 'Hasil Murāja‘ah tersimpan. Jadwal penjagaan berikutnya sudah disiapkan.');
    }

    public function storeMemorization(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudent($request, $student);
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);

        $data = $request->validate([
            'memorization_target_id' => ['nullable','exists:memorization_targets,id'],
            'marhalah_type_id' => ['nullable','exists:marhalah_types,id'],
            'record_type' => ['required', Rule::in(['new_memorization','initial_repetition','home_submission','class_submission','tasmi','exam'])],
            'delivery_mode' => ['required', Rule::in(['talaqqi','individual_submission','group_tasmi','home_submission','exam'])],
            'surah_id' => ['required','exists:quran_surahs,id'],
            'start_verse' => ['required','integer','min:1'],
            'end_verse' => ['required','integer','gte:start_verse'],
            'result' => ['required', Rule::in(['fluent','fair','repeat_needed','postponed'])],
            'fluency_status' => ['nullable', Rule::in(['strong','developing','needs_repetition'])],
            'tajwid_status' => ['nullable', Rule::in(['strong','developing','needs_correction'])],
            'error_count' => ['nullable','integer','min:0','max:999'],
            'prompt_count' => ['nullable','integer','min:0','max:999'],
            'self_correction_count' => ['nullable','integer','min:0','max:999'],
            'assistance_level' => ['required', Rule::in(['none','little','several','much'])],
            'follow_up' => ['nullable','string','max:190'],
            'review_recommendation' => ['nullable','string','max:190'],
            'next_review_date' => ['nullable','date','after_or_equal:today'],
            'teacher_notes' => ['nullable','string','max:5000'],
            'portion_confirmed' => ['nullable','boolean'],
            'error_categories' => ['nullable','array'],
            'error_categories.*' => ['string','max:50'],
            'error_ayah' => ['nullable','integer','min:1'],
            'error_note' => ['nullable','string','max:1000'],
        ]);
        $this->validateVerseRange((int) $data['surah_id'], (int) $data['end_verse']);

        $resolvedJourney = null;
        if ($data['record_type'] === 'new_memorization') {
            $resolvedJourney = $this->journey->resolveRange($student,(int)$data['surah_id'],(int)$data['start_verse'],(int)$data['end_verse'],true);
        }

        $target = null;
        if (! empty($data['memorization_target_id'])) {
            $target = MemorizationTarget::query()
                ->where('institution_id', $request->user()->institution_id)
                ->where('student_id', $student->id)
                ->findOrFail($data['memorization_target_id']);
            abort_if(
                (int) $target->surah_id !== (int) $data['surah_id']
                || (int) $target->start_verse !== (int) $data['start_verse']
                || (int) $target->end_verse !== (int) $data['end_verse'],
                422,
                'Target yang dipilih tidak sama dengan rentang ayat setoran. Pilih target lain atau kosongkan Target terkait.',
            );
            $data['marhalah_type_id'] ??= $target->marhalah_type_id;
            $data['portion_confirmed'] = $target->portion_confirmed || (bool)($data['portion_confirmed'] ?? false);
        }
        if ($data['record_type'] === 'new_memorization') {
            $data['marhalah_type_id'] = $resolvedJourney['marhalah']?->id ?? $data['marhalah_type_id'] ?? null;
            if (! $target && ! (bool)($data['portion_confirmed'] ?? false)) {
                return back()->withErrors(['portion_confirmed'=>'Konfirmasi bahwa porsi setoran sesuai Marhalah '.$resolvedJourney['rule']['name'].' ('.$resolvedJourney['rule']['portion'].') pada Mushaf Madinah.'])->withInput();
            }
        }
        $target ??= MemorizationTarget::query()
            ->where('institution_id', $request->user()->institution_id)
            ->where('student_id', $student->id)
            ->where('surah_id', $data['surah_id'])
            ->where('start_verse', $data['start_verse'])
            ->where('end_verse', $data['end_verse'])
            ->whereIn('status', ['active','in_progress','strengthening','paused'])
            ->latest()->first();

        $cycle = $this->learning->resolveCycle(
            (int) $request->user()->institution_id,
            (int) $student->id,
            $teacher,
            $target,
            in_array($data['record_type'], ['tasmi','exam'], true) ? $data['record_type'] : 'new_memorization',
            match ($data['delivery_mode']) {
                'talaqqi' => 'talaqqi',
                'home_submission' => 'mixed',
                'group_tasmi' => 'teach_back',
                default => 'reading_repetition',
            },
        );

        $recordData = $data;
        unset($recordData['error_categories'], $recordData['error_ayah'], $recordData['error_note'], $recordData['portion_confirmed']);
        $record = MemorizationRecord::create([
            ...$recordData,
            'memorization_target_id' => $target?->id,
            'learning_cycle_id' => $cycle->id,
            'institution_id' => $request->user()->institution_id,
            'meeting_id' => null,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'recorded_at' => now(),
        ]);

        $this->learning->applyMemorizationOutcome($cycle, $record);
        $this->learning->scheduleReviewFromMemorization($record, $teacher);
        $this->learning->recordErrors(
            'memorization', $record->id, (int) $request->user()->institution_id, (int) $student->id,
            (int) $teacher->id, null, $data['error_categories'] ?? [],
            $data['error_ayah'] ?? null, $data['error_note'] ?? null,
        );

        if ($target) {
            $status = match ($data['result']) {
                'fluent' => 'completed',
                'fair' => 'in_progress',
                'repeat_needed' => 'strengthening',
                default => 'paused',
            };
            $target->update(['status' => $status, 'completed_at' => $status === 'completed' ? now() : null]);
            $this->journey->refreshPortionForTarget($target->fresh());
        }

        $this->log($request, 'tahfizh.individual_memorization_recorded', $record, [
            'student_id' => $student->id,
            'target_id' => $target?->id,
            'source' => 'student_journey',
        ]);

        return redirect()->route('teacher.tahfizh.student', $student)
            ->with('success', 'Setoran Tahfizh tersimpan di perjalanan santri.');
    }

    public function storeMurajaah(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudent($request, $student);
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);

        $data = $request->validate([
            'review_plan_id' => ['nullable','exists:memorization_review_plans,id'],
            'murajaah_type' => ['required', Rule::in(['scheduled','random_recall','continuation','tasmi','home'])],
            'surah_id' => ['required','exists:quran_surahs,id'],
            'start_verse' => ['required','integer','min:1'],
            'end_verse' => ['required','integer','gte:start_verse'],
            'result' => ['required', Rule::in(['maintained','strengthening_needed','reactivation_needed'])],
            'strength_status' => ['nullable', Rule::in(['strong','fair','weak','recall_needed'])],
            'assistance_level' => ['required', Rule::in(['none','little','several','much'])],
            'prompt_count' => ['nullable','integer','min:0','max:999'],
            'self_correction_count' => ['nullable','integer','min:0','max:999'],
            'next_review_date' => ['nullable','date','after_or_equal:today'],
            'review_recommendation' => ['nullable','string','max:190'],
            'teacher_notes' => ['nullable','string','max:5000'],
            'error_categories' => ['nullable','array'],
            'error_categories.*' => ['string','max:50'],
            'error_ayah' => ['nullable','integer','min:1'],
            'error_note' => ['nullable','string','max:1000'],
        ]);
        $this->validateVerseRange((int) $data['surah_id'], (int) $data['end_verse']);

        $reviewPlan = null;
        if (! empty($data['review_plan_id'])) {
            $reviewPlan = MemorizationReviewPlan::query()
                ->where('institution_id', $request->user()->institution_id)
                ->where('student_id', $student->id)
                ->where('status', 'scheduled')
                ->findOrFail($data['review_plan_id']);
            abort_if(
                (int) $reviewPlan->surah_id !== (int) $data['surah_id']
                || (int) $reviewPlan->start_verse !== (int) $data['start_verse']
                || (int) $reviewPlan->end_verse !== (int) $data['end_verse'],
                422,
                'Jadwal Murāja‘ah yang dipilih tidak sama dengan rentang ayat yang dicatat.',
            );
        }

        $target = MemorizationTarget::query()
            ->where('institution_id', $request->user()->institution_id)
            ->where('student_id', $student->id)
            ->where('surah_id', $data['surah_id'])
            ->where('start_verse', $data['start_verse'])
            ->where('end_verse', $data['end_verse'])
            ->whereIn('status', ['active','in_progress','strengthening','paused'])
            ->latest()->first();

        $cycle = $this->learning->resolveCycle(
            (int) $request->user()->institution_id,
            (int) $student->id,
            $teacher,
            $target,
            'murajaah',
            'reading_repetition',
        );

        $recordData = $data;
        unset($recordData['error_categories'], $recordData['error_ayah'], $recordData['error_note']);
        $record = MurajaahRecord::create([
            ...$recordData,
            'learning_cycle_id' => $cycle->id,
            'review_plan_id' => $reviewPlan?->id,
            'institution_id' => $request->user()->institution_id,
            'meeting_id' => null,
            'student_id' => $student->id,
            'teacher_id' => $teacher->id,
            'recorded_at' => now(),
        ]);

        $this->learning->completeReviewPlan($reviewPlan, $record);
        $this->learning->scheduleNextReview($record, $teacher);
        $this->learning->applyMurajaahOutcome($cycle, $record);
        $this->learning->recordErrors(
            'murajaah', $record->id, (int) $request->user()->institution_id, (int) $student->id,
            (int) $teacher->id, null, $data['error_categories'] ?? [],
            $data['error_ayah'] ?? null, $data['error_note'] ?? null,
        );

        if ($target && in_array($target->target_type, ['murajaah','initial_repetition'], true)) {
            $status = match ($data['result']) {
                'maintained' => 'completed',
                'strengthening_needed' => 'strengthening',
                default => 'in_progress',
            };
            $target->update(['status' => $status, 'completed_at' => $status === 'completed' ? now() : null]);
            $this->journey->refreshPortionForTarget($target->fresh());
        }

        $this->log($request, 'tahfizh.individual_murajaah_recorded', $record, [
            'student_id' => $student->id,
            'review_plan_id' => $reviewPlan?->id,
            'source' => 'student_journey',
        ]);

        return redirect()->route('teacher.tahfizh.student', $student)
            ->with('success', 'Murāja‘ah tersimpan di perjalanan santri.');
    }

    public function storeReviewPlan(Request $request): RedirectResponse
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);
        $data = $request->validate([
            'student_id' => ['required','exists:students,id'],
            'memorization_target_id' => ['nullable','exists:memorization_targets,id'],
            'surah_id' => ['required','exists:quran_surahs,id'],
            'start_verse' => ['required','integer','min:1'],
            'end_verse' => ['required','integer','gte:start_verse'],
            'review_date' => ['required','date','after_or_equal:today'],
            'review_type' => ['required', Rule::in(['scheduled','random_recall','continuation','tasmi','home'])],
            'priority' => ['required', Rule::in(['normal','strengthen','recall'])],
            'notes' => ['nullable','string','max:2000'],
        ]);
        $student = Student::where('institution_id', $request->user()->institution_id)->findOrFail($data['student_id']);
        $this->authorizeStudent($request, $student);
        $surah = QuranSurah::findOrFail($data['surah_id']);
        abort_if((int) $data['end_verse'] > (int) $surah->verse_count, 422, 'Rentang ayat melebihi jumlah ayat surah.');

        if (! empty($data['memorization_target_id'])) {
            MemorizationTarget::query()
                ->where('institution_id', $request->user()->institution_id)
                ->where('student_id', $student->id)
                ->findOrFail($data['memorization_target_id']);
        }

        MemorizationReviewPlan::updateOrCreate(
            [
                'institution_id' => $request->user()->institution_id,
                'student_id' => $student->id,
                'surah_id' => $data['surah_id'],
                'start_verse' => $data['start_verse'],
                'end_verse' => $data['end_verse'],
                'review_date' => $data['review_date'],
                'status' => 'scheduled',
            ],
            [
                'created_by_teacher_id' => $teacher->id,
                'memorization_target_id' => $data['memorization_target_id'] ?? null,
                'review_type' => $data['review_type'],
                'priority' => $data['priority'],
                'notes' => $data['notes'] ?? null,
            ]
        );
        return redirect()->route('teacher.tahfizh.student', $student)->with('success', 'Jadwal Murāja‘ah berhasil dibuat.');
    }

    public function updateReviewPlan(Request $request, MemorizationReviewPlan $plan): RedirectResponse
    {
        abort_unless((int) $plan->institution_id === (int) $request->user()->institution_id, 404);
        $student = Student::findOrFail($plan->student_id);
        $this->authorizeStudent($request, $student);
        $data = $request->validate([
            'status' => ['required', Rule::in(['scheduled','completed','skipped','rescheduled','cancelled'])],
            'review_date' => ['nullable','date'],
            'notes' => ['nullable','string','max:2000'],
        ]);
        $plan->update([
            'status' => $data['status'],
            'review_date' => $data['review_date'] ?? $plan->review_date,
            'notes' => $data['notes'] ?? $plan->notes,
        ]);
        return back()->with('success', 'Jadwal Murāja‘ah diperbarui.');
    }

    public function resolveError(Request $request, QuranLearningErrorItem $error): RedirectResponse
    {
        abort_unless((int) $error->institution_id === (int) $request->user()->institution_id, 404);
        $student = Student::findOrFail($error->student_id);
        $this->authorizeStudent($request, $student);
        $error->update(['resolved_at' => now()]);
        return back()->with('success', 'Fokus koreksi ditandai sudah ditindaklanjuti.');
    }

    private function validateVerseRange(int $surahId, int $endVerse): void
    {
        $surah = QuranSurah::findOrFail($surahId);
        abort_if($endVerse > $surah->verse_count, 422, 'Rentang ayat melebihi jumlah ayat surah.');
    }

    /** @return array<string, mixed> */
    private function validateQuickSubmission(Request $request, bool $memorization): array
    {
        $data = $request->validate([
            'submission_key' => ['required', 'uuid', 'max:64'],
            'memorization_target_id' => [$memorization ? 'nullable' : 'exclude', 'integer', 'exists:memorization_targets,id'],
            'review_plan_id' => [$memorization ? 'exclude' : 'nullable', 'integer', 'exists:memorization_review_plans,id'],
            'surah_id' => ['required', 'integer', 'exists:quran_surahs,id'],
            'start_verse' => ['required', 'integer', 'min:1'],
            'end_verse' => ['required', 'integer', 'gte:start_verse'],
            'daily_decision' => ['required', Rule::in(['lanjut', 'kuatkan', 'ulang'])],
            'short_note' => ['nullable', 'string', 'max:500'],
        ]);
        $this->validateVerseRange((int) $data['surah_id'], (int) $data['end_verse']);

        return $data;
    }

    private function log(Request $request, string $action, object $subject, array $newValues = []): void
    {
        ActivityLog::create([
            'institution_id' => $request->user()->institution_id,
            'user_id' => $request->user()->id,
            'action' => $action,
            'subject_type' => $subject::class,
            'subject_id' => $subject->id ?? null,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }

    /** @return Collection<int, Student> */
    private function students(Request $request): Collection
    {
        $ids = $this->studentIds($request);
        return Student::query()
            ->with('currentEnrollment.schoolClass')
            ->where('institution_id', $request->user()->institution_id)
            ->whereIn('id', $ids)
            ->orderBy('full_name')
            ->get();
    }

    private function authorizeStudent(Request $request, Student $student): void
    {
        abort_unless(
            (int) $student->institution_id === (int) $request->user()->institution_id
            && $this->studentIds($request)->contains($student->id),
            403,
            'Santri tidak termasuk dalam penugasan guru ini.',
        );
    }

    /** @return Collection<int, int> */
    private function studentIds(Request $request): Collection
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);
        $assignments = TeacherAssignment::query()
            ->where('institution_id', $request->user()->institution_id)
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('valid_from')->orWhereDate('valid_from', '<=', today()))
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhereDate('valid_until', '>=', today()))
            ->get();

        $classIds = $assignments->pluck('class_id')->filter();
        $groupIds = $assignments->pluck('learning_group_id')->filter();
        $fromClasses = ClassEnrollment::query()->whereIn('class_id', $classIds)->where('status', 'active')->pluck('student_id');
        $fromGroups = GroupMembership::query()->whereIn('learning_group_id', $groupIds)->where('status', 'active')->pluck('student_id');
        return $fromClasses->merge($fromGroups)->unique()->values();
    }
}
