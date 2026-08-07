<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AttendanceRecord;
use App\Models\LearningGroup;
use App\Models\MarhalahType;
use App\Models\Meeting;
use App\Models\MemorizationRecord;
use App\Models\MemorizationReviewPlan;
use App\Models\MemorizationTarget;
use App\Models\MurajaahRecord;
use App\Models\QuranSurah;
use App\Models\SchoolClass;
use App\Models\TahsinRecord;
use App\Services\TahfizhLearningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MeetingController extends Controller
{
    public function __construct(private readonly TahfizhLearningService $tahfizh)
    {
    }

    public function create(Request $request): View
    {
        $targetType = $request->string('target_type')->toString();
        $targetId = $request->integer('target_id');
        [$target, $assignment] = $this->resolveTarget($request, $targetType, $targetId);
        return view('teacher.meetings.create', compact('target', 'targetType', 'assignment'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'target_type' => ['required', Rule::in(['class', 'group'])],
            'target_id' => ['required', 'integer'],
            'meeting_date' => ['required', 'date'],
            'started_at' => ['nullable', 'date_format:H:i'],
            'topic' => ['nullable', 'string', 'max:190'],
            'meeting_type' => ['required', Rule::in(['tahsin','tahfizh','murajaah','friday_development','additional','general'])],
        ]);
        [$target, $assignment] = $this->resolveTarget($request, $data['target_type'], $data['target_id']);

        $meeting = Meeting::create([
            'institution_id' => $request->user()->institution_id,
            'class_id' => $data['target_type'] === 'class' ? $target->id : null,
            'learning_group_id' => $data['target_type'] === 'group' ? $target->id : null,
            'program_id' => $assignment->program_id,
            'teacher_id' => $request->user()->teacher->id,
            'meeting_date' => $data['meeting_date'],
            'started_at' => $data['started_at'] ? now()->setTimeFromTimeString($data['started_at']) : now(),
            'topic' => $data['topic'],
            'meeting_type' => $data['meeting_type'],
            'status' => 'ongoing',
        ]);

        $this->log($request, 'meeting.created', $meeting, ['target' => $target->name, 'type' => $data['meeting_type']]);

        return redirect()->route('teacher.meetings.attendance', $meeting)
            ->with('success', 'Pertemuan dimulai. Tandai semua hadir, lalu ubah santri yang tidak hadir.');
    }

    public function show(Request $request, Meeting $meeting): View
    {
        $this->authorizeMeeting($request, $meeting);
        $students = $this->students($meeting);
        $meeting->load(['attendanceRecords','tahsinRecords','memorizationRecords','murajaahRecords','schoolClass','learningGroup','teacher']);

        return view('teacher.meetings.show', [
            'meeting' => $meeting,
            'students' => $students,
            'surahs' => QuranSurah::orderBy('id')->get(),
            'marhalah' => MarhalahType::where('status', 'active')->orderBy('sequence')->get(),
            'targets' => MemorizationTarget::with(['student','rubu','surah','marhalah'])
                ->where('institution_id', $meeting->institution_id)
                ->whereIn('student_id', $students->pluck('id'))
                ->whereIn('status', ['active','in_progress','strengthening','paused'])
                ->latest()->get(),
            'reviewPlans' => MemorizationReviewPlan::with(['student','surah','target'])
                ->where('institution_id', $meeting->institution_id)
                ->whereIn('student_id', $students->pluck('id'))
                ->where('status', 'scheduled')
                ->whereDate('review_date', '<=', today())
                ->orderBy('review_date')->get(),
        ]);
    }

    public function attendance(Request $request, Meeting $meeting): View
    {
        $this->authorizeMeeting($request, $meeting);
        $students = $this->students($meeting);
        $existing = $meeting->attendanceRecords()->get()->keyBy('student_id');
        return view('teacher.meetings.attendance', compact('meeting', 'students', 'existing'));
    }

    public function storeAttendance(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorizeMeeting($request, $meeting);
        $allowedStudentIds = $this->students($meeting)->pluck('id');
        $data = $request->validate([
            'attendance' => ['required', 'array'],
            'attendance.*.status' => ['required', Rule::in(['present','late','permission','sick','absent'])],
            'attendance.*.notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($data, $allowedStudentIds, $meeting, $request): void {
            foreach ($data['attendance'] as $studentId => $record) {
                if (! $allowedStudentIds->contains((int) $studentId)) {
                    continue;
                }
                AttendanceRecord::updateOrCreate(
                    ['meeting_id' => $meeting->id, 'student_id' => $studentId],
                    ['status' => $record['status'], 'notes' => $record['notes'] ?? null, 'recorded_by' => $request->user()->id]
                );
            }
            $meeting->update(['attendance_completed_at' => now()]);
        });

        $this->log($request, 'meeting.attendance_saved', $meeting, ['records' => count($data['attendance'])]);
        return redirect()->route('teacher.meetings.show', $meeting)->with('success', 'Absensi lengkap dan berhasil disimpan.');
    }

    public function storeTahsin(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorizeMeeting($request, $meeting);
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'material_text' => ['required', 'string', 'max:500'],
            'surah_id' => ['nullable', 'exists:quran_surahs,id'],
            'start_verse' => ['nullable', 'integer', 'min:1'],
            'end_verse' => ['nullable', 'integer', 'gte:start_verse'],
            'overall_status' => ['required', Rule::in(['good','practice_needed','guidance_needed','special_correction'])],
            'fluency_status' => ['nullable', Rule::in(['strong','developing','needs_guidance'])],
            'makhraj_status' => ['nullable', Rule::in(['strong','developing','needs_guidance'])],
            'tajwid_status' => ['nullable', Rule::in(['strong','developing','needs_guidance'])],
            'adab_status' => ['nullable', Rule::in(['consistent','developing','needs_guidance'])],
            'decision' => ['nullable', Rule::in(['continue','repeat','strengthen'])],
            'focus_categories' => ['nullable', 'array'],
            'focus_categories.*' => ['string', 'max:50'],
            'teacher_notes' => ['nullable', 'string'],
            'follow_up' => ['nullable', 'string', 'max:190'],
            'error_categories' => ['nullable', 'array'],
            'error_categories.*' => ['string', 'max:50'],
            'error_ayah' => ['nullable', 'integer', 'min:1'],
            'error_note' => ['nullable', 'string', 'max:1000'],
        ]);
        $this->authorizeStudent($meeting, (int) $data['student_id']);
        $recordData = $data;
        unset($recordData['error_categories'], $recordData['error_ayah'], $recordData['error_note']);
        $record = TahsinRecord::create([
            ...$recordData,
            'institution_id' => $meeting->institution_id,
            'meeting_id' => $meeting->id,
            'teacher_id' => $request->user()->teacher->id,
        ]);
        $this->tahfizh->recordErrors(
            'tahsin', $record->id, (int) $meeting->institution_id, (int) $data['student_id'],
            (int) $request->user()->teacher->id, (int) $meeting->id, $data['error_categories'] ?? [],
            $data['error_ayah'] ?? null, $data['error_note'] ?? null,
        );
        $this->log($request, 'tahsin.recorded', $record, ['student_id' => $data['student_id']]);
        return back()->with('success', 'Catatan Tahsīn berhasil disimpan.');
    }

    public function storeMemorization(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorizeMeeting($request, $meeting);
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'memorization_target_id' => ['nullable', 'exists:memorization_targets,id'],
            'marhalah_type_id' => ['nullable', 'exists:marhalah_types,id'],
            'record_type' => ['required', Rule::in(['new_memorization','initial_repetition','home_submission','class_submission','tasmi','exam'])],
            'delivery_mode' => ['required', Rule::in(['talaqqi','individual_submission','group_tasmi','home_submission','exam'])],
            'surah_id' => ['required', 'exists:quran_surahs,id'],
            'start_verse' => ['required', 'integer', 'min:1'],
            'end_verse' => ['required', 'integer', 'gte:start_verse'],
            'result' => ['required', Rule::in(['fluent','fair','repeat_needed','postponed'])],
            'fluency_status' => ['nullable', Rule::in(['strong','developing','needs_repetition'])],
            'tajwid_status' => ['nullable', Rule::in(['strong','developing','needs_correction'])],
            'error_count' => ['nullable', 'integer', 'min:0', 'max:999'],
            'prompt_count' => ['nullable', 'integer', 'min:0', 'max:999'],
            'self_correction_count' => ['nullable', 'integer', 'min:0', 'max:999'],
            'assistance_level' => ['required', Rule::in(['none','little','several','much'])],
            'follow_up' => ['nullable', 'string', 'max:190'],
            'review_recommendation' => ['nullable', 'string', 'max:190'],
            'next_review_date' => ['nullable', 'date', 'after_or_equal:today'],
            'teacher_notes' => ['nullable', 'string'],
            'error_categories' => ['nullable', 'array'],
            'error_categories.*' => ['string', 'max:50'],
            'error_ayah' => ['nullable', 'integer', 'min:1'],
            'error_note' => ['nullable', 'string', 'max:1000'],
        ]);
        $this->authorizeStudent($meeting, (int) $data['student_id']);
        $this->validateVerseRange((int) $data['surah_id'], (int) $data['end_verse']);

        $target = null;
        if (! empty($data['memorization_target_id'])) {
            $target = MemorizationTarget::query()
                ->where('institution_id', $meeting->institution_id)
                ->where('student_id', $data['student_id'])
                ->findOrFail($data['memorization_target_id']);
        }
        $target ??= MemorizationTarget::where('institution_id', $meeting->institution_id)
            ->where('student_id', $data['student_id'])
            ->where('surah_id', $data['surah_id'])
            ->where('start_verse', $data['start_verse'])
            ->where('end_verse', $data['end_verse'])
            ->whereIn('status', ['active','in_progress','strengthening','paused'])
            ->latest()->first();

        $cycle = $this->tahfizh->resolveCycle(
            (int) $meeting->institution_id, (int) $data['student_id'], $request->user()->teacher, $target,
            in_array($data['record_type'], ['tasmi','exam'], true) ? $data['record_type'] : 'new_memorization',
            match ($data['delivery_mode']) {
                'talaqqi' => 'talaqqi',
                'home_submission' => 'mixed',
                'group_tasmi' => 'teach_back',
                default => 'reading_repetition',
            },
        );
        $recordData = $data;
        unset($recordData['error_categories'], $recordData['error_ayah'], $recordData['error_note']);
        $record = MemorizationRecord::create([
            ...$recordData,
            'memorization_target_id' => $target?->id,
            'learning_cycle_id' => $cycle->id,
            'institution_id' => $meeting->institution_id,
            'meeting_id' => $meeting->id,
            'teacher_id' => $request->user()->teacher->id,
            'recorded_at' => now(),
        ]);

        $this->tahfizh->applyMemorizationOutcome($cycle, $record);
        $this->tahfizh->scheduleReviewFromMemorization($record, $request->user()->teacher);
        $this->tahfizh->recordErrors(
            'memorization', $record->id, (int) $meeting->institution_id, (int) $data['student_id'],
            (int) $request->user()->teacher->id, (int) $meeting->id, $data['error_categories'] ?? [],
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
        }

        $this->log($request, 'memorization.recorded', $record, ['student_id' => $data['student_id'], 'target_id' => $target?->id]);
        return back()->with('success', 'Setoran Tahfizh berhasil disimpan dan target terkait diperbarui.');
    }

    public function storeMurajaah(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorizeMeeting($request, $meeting);
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'review_plan_id' => ['nullable', 'exists:memorization_review_plans,id'],
            'murajaah_type' => ['required', Rule::in(['scheduled','random_recall','continuation','tasmi','home'])],
            'surah_id' => ['required', 'exists:quran_surahs,id'],
            'start_verse' => ['required', 'integer', 'min:1'],
            'end_verse' => ['required', 'integer', 'gte:start_verse'],
            'result' => ['required', Rule::in(['maintained','strengthening_needed','reactivation_needed'])],
            'strength_status' => ['nullable', Rule::in(['strong','fair','weak','recall_needed'])],
            'assistance_level' => ['required', Rule::in(['none','little','several','much'])],
            'prompt_count' => ['nullable', 'integer', 'min:0', 'max:999'],
            'self_correction_count' => ['nullable', 'integer', 'min:0', 'max:999'],
            'next_review_date' => ['nullable', 'date', 'after_or_equal:today'],
            'review_recommendation' => ['nullable', 'string', 'max:190'],
            'teacher_notes' => ['nullable', 'string'],
            'error_categories' => ['nullable', 'array'],
            'error_categories.*' => ['string', 'max:50'],
            'error_ayah' => ['nullable', 'integer', 'min:1'],
            'error_note' => ['nullable', 'string', 'max:1000'],
        ]);
        $this->authorizeStudent($meeting, (int) $data['student_id']);
        $this->validateVerseRange((int) $data['surah_id'], (int) $data['end_verse']);

        $reviewPlan = null;
        if (! empty($data['review_plan_id'])) {
            $reviewPlan = MemorizationReviewPlan::query()
                ->where('institution_id', $meeting->institution_id)
                ->where('student_id', $data['student_id'])
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

        $target = MemorizationTarget::where('institution_id', $meeting->institution_id)
            ->where('student_id', $data['student_id'])
            ->where('surah_id', $data['surah_id'])
            ->where('start_verse', $data['start_verse'])
            ->where('end_verse', $data['end_verse'])
            ->whereIn('status', ['active','in_progress','strengthening','paused'])
            ->latest()->first();
        $cycle = $this->tahfizh->resolveCycle(
            (int) $meeting->institution_id, (int) $data['student_id'], $request->user()->teacher, $target, 'murajaah', 'reading_repetition'
        );
        $recordData = $data;
        unset($recordData['error_categories'], $recordData['error_ayah'], $recordData['error_note']);
        $record = MurajaahRecord::create([
            ...$recordData,
            'learning_cycle_id' => $cycle->id,
            'review_plan_id' => $reviewPlan?->id,
            'institution_id' => $meeting->institution_id,
            'meeting_id' => $meeting->id,
            'teacher_id' => $request->user()->teacher->id,
            'recorded_at' => now(),
        ]);
        $this->tahfizh->completeReviewPlan($reviewPlan, $record);
        $this->tahfizh->scheduleNextReview($record, $request->user()->teacher);
        $this->tahfizh->applyMurajaahOutcome($cycle, $record);
        $this->tahfizh->recordErrors(
            'murajaah', $record->id, (int) $meeting->institution_id, (int) $data['student_id'],
            (int) $request->user()->teacher->id, (int) $meeting->id, $data['error_categories'] ?? [],
            $data['error_ayah'] ?? null, $data['error_note'] ?? null,
        );

        if ($target && in_array($target->target_type, ['murajaah','initial_repetition'], true)) {
            $status = match ($data['result']) {
                'maintained' => 'completed',
                'strengthening_needed' => 'strengthening',
                default => 'in_progress',
            };
            $target->update(['status' => $status, 'completed_at' => $status === 'completed' ? now() : null]);
        }

        $this->log($request, 'murajaah.recorded', $record, ['student_id' => $data['student_id']]);
        return back()->with('success', 'Catatan Murāja‘ah berhasil disimpan.');
    }

    public function finish(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorizeMeeting($request, $meeting);
        $data = $request->validate([
            'general_notes' => ['nullable', 'string', 'max:5000'],
            'guardian_summary' => ['nullable', 'string', 'max:3000'],
            'publish_summary' => ['nullable', 'boolean'],
        ]);

        $studentCount = $this->students($meeting)->count();
        abort_if($meeting->attendanceRecords()->count() < $studentCount, 422, 'Lengkapi absensi seluruh santri sebelum menutup pertemuan.');

        $meeting->update([
            'general_notes' => $data['general_notes'] ?? null,
            'guardian_summary' => $data['guardian_summary'] ?? null,
            'ended_at' => now(),
            'learning_completed_at' => now(),
            'summary_published_at' => ! empty($data['publish_summary']) ? now() : null,
            'closed_by_user_id' => $request->user()->id,
            'status' => 'completed',
        ]);

        $this->log($request, 'meeting.completed', $meeting, ['summary_published' => (bool) ($data['publish_summary'] ?? false)]);
        return redirect()->route('teacher.daily.index')->with('success', 'Pertemuan telah ditutup dan ringkasan tersimpan.');
    }

    private function resolveTarget(Request $request, string $type, int $id): array
    {
        if ($type === 'class') {
            $target = SchoolClass::query()
                ->where('institution_id', $request->user()->institution_id)
                ->findOrFail($id);
            return [$target, ClassroomController::classAssignment($request, $target)];
        }
        if ($type === 'group') {
            $target = LearningGroup::query()
                ->where('institution_id', $request->user()->institution_id)
                ->findOrFail($id);
            return [$target, ClassroomController::groupAssignment($request, $target)];
        }
        abort(422, 'Jenis target tidak sesuai.');
    }

    private function students(Meeting $meeting)
    {
        if ($meeting->class_id) {
            return $meeting->schoolClass->activeEnrollments()->with('student')->get()->pluck('student')->sortBy('full_name')->values();
        }
        return $meeting->learningGroup->activeMemberships()->with('student')->get()->pluck('student')->sortBy('full_name')->values();
    }

    private function authorizeMeeting(Request $request, Meeting $meeting): void
    {
        abort_unless(
            $request->user()->teacher
            && (int) $meeting->institution_id === (int) $request->user()->institution_id
            && (int) $meeting->teacher_id === (int) $request->user()->teacher->id,
            403,
        );
    }

    private function authorizeStudent(Meeting $meeting, int $studentId): void
    {
        abort_unless($this->students($meeting)->pluck('id')->contains($studentId), 403, 'Santri tidak termasuk dalam pertemuan ini.');
    }

    private function validateVerseRange(int $surahId, int $endVerse): void
    {
        $surah = QuranSurah::findOrFail($surahId);
        abort_if($endVerse > $surah->verse_count, 422, 'Rentang ayat melebihi jumlah ayat surah.');
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
}
