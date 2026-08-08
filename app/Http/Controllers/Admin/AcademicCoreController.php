<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\LearningObservation;
use App\Models\MarhalahType;
use App\Models\MemorizationTarget;
use App\Models\QuranRubu;
use App\Models\QuranSurah;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Services\QuranJourneyService;

class AcademicCoreController extends Controller
{
    public function __construct(private readonly QuranJourneyService $journey)
    {
    }
    public function index(Request $request): View
    {
        $institutionId = $request->user()->institution_id;
        $activeYear = AcademicYear::where('institution_id', $institutionId)->where('is_active', true)->first();

        return view('admin.academic-core.index', [
            'institution' => $request->user()->institution,
            'activeYear' => $activeYear,
            'years' => AcademicYear::where('institution_id', $institutionId)->latest('start_date')->get(),
            'students' => Student::with('currentEnrollment.schoolClass')->where('institution_id', $institutionId)->where('status', 'active')->orderBy('full_name')->get(),
            'rubus' => QuranRubu::with(['startSurah','endSurah'])->where('status', 'active')->orderBy('rubu_number')->get(),
            'surahs' => QuranSurah::orderBy('id')->get(),
            'marhalah' => MarhalahType::where('status', 'active')->orderBy('sequence')->get(),
            'targets' => MemorizationTarget::with(['student.currentEnrollment.schoolClass','rubu','surah','marhalah','assignedByTeacher'])
                ->where('institution_id', $institutionId)->latest()->limit(40)->get(),
            'activeTargetCount' => MemorizationTarget::where('institution_id', $institutionId)->whereIn('status', ['active','in_progress','strengthening'])->count(),
            'completedTargetCount' => MemorizationTarget::where('institution_id', $institutionId)->where('status', 'completed')->count(),
            'observationCount' => LearningObservation::where('institution_id', $institutionId)->count(),
            'untestedStifinCount' => Student::where('institution_id', $institutionId)->where('status', 'active')->where('stifin_status', 'untested')->count(),
        ]);
    }

    public function updateYear(Request $request, AcademicYear $year): RedirectResponse
    {
        abort_unless($year->institution_id === $request->user()->institution_id, 404);
        $data = $request->validate([
            'active_semester' => ['required', Rule::in(['semester_1','semester_2','antara_semester'])],
            'enrollment_status' => ['required', Rule::in(['closed','open','internal_only'])],
        ]);
        $year->update($data);
        return back()->with('success', 'Semester aktif dan status pendaftaran berhasil diperbarui.');
    }

    public function storeTarget(Request $request): RedirectResponse
    {
        $institutionId = $request->user()->institution_id;
        $activeYear = AcademicYear::where('institution_id', $institutionId)->where('is_active', true)->firstOrFail();
        $data = $this->validateTarget($request);
        $student = Student::where('institution_id', $institutionId)->findOrFail($data['student_id']);
        $this->validateVerseRange((int) $data['surah_id'], (int) $data['end_verse']);

        if ($data['target_type'] === 'new_memorization') {
            if (! (bool)($data['portion_confirmed'] ?? false)) {
                return back()->withErrors(['portion_confirmed'=>'Konfirmasi porsi Marhalah pada Mushaf Madinah sebelum membuat target hafalan baru.'])->withInput();
            }
            $resolved = $this->journey->resolveRange($student,(int)$data['surah_id'],(int)$data['start_verse'],(int)$data['end_verse'],true);
            $data['marhalah_type_id'] = $resolved['marhalah']?->id;
            $data['journey_juz_number'] = $resolved['juz'];
            $data['portion_confirmed'] = true;
            $data['portion_note'] = 'Dikonfirmasi admin: '.$resolved['rule']['name'].' · porsi '.$resolved['rule']['portion'].'.';
        } else {
            $data['portion_confirmed'] = false;
        }

        MemorizationTarget::create([
            ...$data,
            'institution_id' => $institutionId,
            'academic_year_id' => $activeYear->id,
            'assigned_by_teacher_id' => null,
            'status' => 'active',
        ]);

        return back()->with('success', 'Target hafalan santri berhasil dibuat.');
    }

    public function updateTarget(Request $request, MemorizationTarget $target): RedirectResponse
    {
        abort_unless($target->institution_id === $request->user()->institution_id, 404);
        $data = $request->validate([
            'status' => ['required', Rule::in(['active','in_progress','strengthening','completed','paused','cancelled'])],
            'notes' => ['nullable','string','max:2000'],
            'portion_confirmed' => ['nullable','boolean'],
        ]);
        $target->update([
            ...$data,
            'completed_at' => $data['status'] === 'completed' ? now() : null,
        ]);
        return back()->with('success', 'Status target berhasil diperbarui.');
    }

    private function validateTarget(Request $request): array
    {
        return $request->validate([
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
        ]);
    }

    private function validateVerseRange(int $surahId, int $endVerse): void
    {
        $surah = QuranSurah::findOrFail($surahId);
        abort_if($endVerse > $surah->verse_count, 422, 'Rentang ayat melebihi jumlah ayat surah.');
    }
}
