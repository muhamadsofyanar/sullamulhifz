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

class AcademicCoreController extends Controller
{
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
            'surahs' => QuranSurah::whereBetween('id', [78,114])->orderByDesc('id')->get(),
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
        abort_unless(Student::where('institution_id', $institutionId)->whereKey($data['student_id'])->exists(), 403);
        $this->validateVerseRange((int) $data['surah_id'], (int) $data['end_verse']);

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
