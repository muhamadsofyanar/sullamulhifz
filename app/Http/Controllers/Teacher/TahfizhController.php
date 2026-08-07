<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassEnrollment;
use App\Models\GroupMembership;
use App\Models\MemorizationReviewPlan;
use App\Models\MemorizationTarget;
use App\Models\QuranLearningErrorItem;
use App\Models\QuranSurah;
use App\Models\Student;
use App\Models\TahfizhLearningCycle;
use App\Models\TeacherAssignment;
use App\Services\TahfizhLearningService;
use App\Services\TahfizhProgressService;
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
            'errors' => QuranLearningErrorItem::query()
                ->where('institution_id', $request->user()->institution_id)
                ->where('student_id', $student->id)
                ->latest()->limit(40)->get(),
            'surahs' => QuranSurah::orderBy('id')->get(),
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
