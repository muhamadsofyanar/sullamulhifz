<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassEnrollment;
use App\Models\GroupMembership;
use App\Models\LearningObservation;
use App\Models\MarhalahType;
use App\Models\MemorizationTarget;
use App\Models\QuranRubu;
use App\Models\QuranSurah;
use App\Models\Student;
use App\Models\TeacherAssignment;
use App\Services\TahfizhLearningService;
use App\Services\QuranJourneyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LearningPlanController extends Controller
{
    public function __construct(
        private readonly TahfizhLearningService $tahfizh,
        private readonly QuranJourneyService $journey,
    ) {
    }

    public function index(Request $request): View
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);
        $studentIds = $this->studentIds($request);
        $activeYear = AcademicYear::where('institution_id', $request->user()->institution_id)->where('is_active', true)->first();

        return view('teacher.learning-plan.index', [
            'teacher' => $teacher,
            'activeYear' => $activeYear,
            'students' => Student::with('currentEnrollment.schoolClass')->where('institution_id', $request->user()->institution_id)->whereIn('id', $studentIds)->orderBy('full_name')->get(),
            'rubus' => QuranRubu::where('status', 'active')->orderBy('rubu_number')->get(),
            'surahs' => QuranSurah::orderBy('id')->get(),
            'marhalah' => MarhalahType::where('status', 'active')->orderBy('sequence')->get(),
            'targets' => MemorizationTarget::with(['student.currentEnrollment.schoolClass','rubu','surah','marhalah'])
                ->where('institution_id', $request->user()->institution_id)
                ->whereIn('student_id', $studentIds)->latest()->limit(60)->get(),
            'observations' => LearningObservation::with('student')->where('institution_id', $request->user()->institution_id)->where('teacher_id', $teacher->id)->latest('observed_at')->limit(30)->get(),
        ]);
    }

    public function storeTarget(Request $request): RedirectResponse
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);
        $activeYear = AcademicYear::where('institution_id', $request->user()->institution_id)->where('is_active', true)->firstOrFail();
        $data = $request->validate([
            'student_id' => ['required','exists:students,id'],
            'quran_rubu_id' => ['nullable','exists:quran_rubus,id'],
            'surah_id' => ['required','exists:quran_surahs,id'],
            'start_verse' => ['required','integer','min:1'],
            'end_verse' => ['required','integer','gte:start_verse'],
            'marhalah_type_id' => ['nullable','exists:marhalah_types,id'],
            'target_type' => ['required', Rule::in(['new_memorization','initial_repetition','murajaah','tasmi','exam'])],
            'target_date' => ['nullable','date'],
            'due_date' => ['nullable','date','after_or_equal:target_date'],
            'notes' => ['nullable','string','max:2000'],
            'portion_confirmed' => ['nullable','boolean'],
        ]);
        $this->authorizeStudent($request, (int) $data['student_id']);
        $student = Student::where('institution_id',$request->user()->institution_id)->findOrFail($data['student_id']);
        $this->validateVerseRange((int) $data['surah_id'], (int) $data['end_verse']);

        if ($data['target_type'] === 'new_memorization') {
            if (! (bool)($data['portion_confirmed'] ?? false)) {
                return back()->withErrors(['portion_confirmed'=>'Konfirmasi porsi Marhalah pada Mushaf Madinah sebelum membuat target hafalan baru.'])->withInput();
            }
            $resolved = $this->journey->resolveRange($student,(int)$data['surah_id'],(int)$data['start_verse'],(int)$data['end_verse'],true);
            $data['marhalah_type_id'] = $resolved['marhalah']?->id;
            $data['journey_juz_number'] = $resolved['juz'];
            $data['portion_confirmed'] = true;
            $data['portion_note'] = 'Dikonfirmasi guru: '.$resolved['rule']['name'].' · porsi '.$resolved['rule']['portion'].'.';
        } else {
            $data['portion_confirmed'] = false;
        }

        $target = MemorizationTarget::create([
            ...$data,
            'institution_id' => $request->user()->institution_id,
            'academic_year_id' => $activeYear->id,
            'assigned_by_teacher_id' => $teacher->id,
            'status' => 'active',
        ]);
        $cycle = $this->tahfizh->resolveCycle(
            (int) $request->user()->institution_id,
            (int) $data['student_id'],
            $teacher,
            $target,
            $data['target_type'],
            'talaqqi',
        );
        if (! empty($data['notes'])) {
            $cycle->update(['teacher_guidance' => $data['notes']]);
        }
        return back()->with('success', 'Target hafalan berhasil diberikan dan siklus belajar telah disiapkan.');
    }

    public function updateTarget(Request $request, MemorizationTarget $target): RedirectResponse
    {
        abort_unless((int) $target->institution_id === (int) $request->user()->institution_id, 404);
        $this->authorizeStudent($request, (int) $target->student_id);
        $data = $request->validate([
            'status' => ['required', Rule::in(['active','in_progress','strengthening','completed','paused','cancelled'])],
            'notes' => ['nullable','string','max:2000'],
        ]);
        $target->update([
            ...$data,
            'completed_at' => $data['status'] === 'completed' ? now() : null,
        ]);
        return back()->with('success', 'Perkembangan target berhasil disimpan.');
    }

    public function storeObservation(Request $request): RedirectResponse
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);
        $activeYear = AcademicYear::where('institution_id', $request->user()->institution_id)->where('is_active', true)->first();
        $data = $request->validate([
            'student_id' => ['required','exists:students,id'],
            'category' => ['required', Rule::in(['learning_method','readiness','focus','communication','family_support'])],
            'method_name' => ['required','string','max:190'],
            'context' => ['nullable','string','max:500'],
            'response' => ['nullable','string','max:2000'],
            'effectiveness' => ['nullable', Rule::in(['helpful','partly_helpful','not_yet_helpful','needs_more_observation'])],
            'notes' => ['nullable','string','max:2000'],
        ]);
        $this->authorizeStudent($request, (int) $data['student_id']);
        LearningObservation::create([
            ...$data,
            'institution_id' => $request->user()->institution_id,
            'academic_year_id' => $activeYear?->id,
            'teacher_id' => $teacher->id,
            'observed_at' => now(),
        ]);
        return back()->with('success', 'Observasi metode belajar berhasil dicatat tanpa memberi label pada santri.');
    }

    private function studentIds(Request $request): Collection
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);
        $assignments = TeacherAssignment::query()
            ->where('institution_id', $request->user()->institution_id)
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->where(function ($query): void {
                $query->whereNull('valid_from')->orWhereDate('valid_from', '<=', today());
            })
            ->where(function ($query): void {
                $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', today());
            })
            ->get();
        $classIds = $assignments->pluck('class_id')->filter();
        $groupIds = $assignments->pluck('learning_group_id')->filter();

        $fromClasses = ClassEnrollment::query()
            ->whereIn('class_id', $classIds)
            ->where('status', 'active')
            ->pluck('student_id');
        $fromGroups = GroupMembership::query()
            ->whereIn('learning_group_id', $groupIds)
            ->where('status', 'active')
            ->pluck('student_id');
        return $fromClasses->merge($fromGroups)->unique()->values();
    }

    private function authorizeStudent(Request $request, int $studentId): void
    {
        abort_unless($this->studentIds($request)->contains($studentId), 403, 'Santri tidak termasuk dalam penugasan guru ini.');
    }

    private function validateVerseRange(int $surahId, int $endVerse): void
    {
        $surah = QuranSurah::findOrFail($surahId);
        abort_if($endVerse > $surah->verse_count, 422, 'Rentang ayat melebihi jumlah ayat surah.');
    }
}
