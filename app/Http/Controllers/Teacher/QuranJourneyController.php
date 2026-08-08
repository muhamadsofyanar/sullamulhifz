<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassEnrollment;
use App\Models\GroupMembership;
use App\Models\MemorizationMilestone;
use App\Models\QuranDivisionUnit;
use App\Models\QuranHeritageTerm;
use App\Models\QuranProgramEnrollment;
use App\Models\QuranProgramTemplate;
use App\Models\QuranJourneyPortion;
use App\Models\QuranSurah;
use App\Models\Student;
use App\Models\StudentMarhalahHistory;
use App\Models\TeacherAssignment;
use App\Services\QuranJourneyService;
use App\Services\MushafLineService;
use App\Services\MushafPageService;
use App\Services\QuranProgramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuranJourneyController extends Controller
{
    public function __construct(
        private readonly QuranJourneyService $journey,
        private readonly QuranProgramService $programs,
        private readonly MushafLineService $mushafLines,
        private readonly MushafPageService $mushafPagesService,
    ) {
    }

    public function index(Request $request): View
    {
        $students = Student::query()
            ->with(['currentEnrollment.schoolClass','quranJourneyProfile.marhalah'])
            ->where('institution_id',$request->user()->institution_id)
            ->whereIn('id',$this->studentIds($request))
            ->orderBy('full_name')
            ->get();

        return view('teacher.quran-journey.index', [
            'students'=>$students,
            'rules'=>$this->journey->stageRules(),
        ]);
    }

    public function student(Request $request, Student $student): View
    {
        $this->authorizeStudent($request,$student);
        $summary = $this->journey->summary($student);
        $student->load('currentEnrollment.schoolClass');

        $lineStatus = $this->mushafLines->status();
        $mushafPages = collect();
        $selectedMushafPage = null;
        $mushafLineBlocks = [];
        $mushafPageOptions = [];
        $profile = $summary['profile'];
        $rule = $summary['rule'];
        if ($profile && in_array(($rule['unit'] ?? null), ['line','page'], true)) {
            $juz = (int) $profile->current_juz_number;
            $mushafPages = $this->mushafLines->pagesForJuz($juz);
            $requestedPage = (int) $request->query('mushaf_page', 0);
            $selectedMushafPage = $mushafPages->contains($requestedPage) ? $requestedPage : $mushafPages->first();
            if ($selectedMushafPage) {
                try {
                    if (($rule['unit'] ?? null) === 'line') {
                        $mushafLineBlocks = $this->mushafLines->blocksForPage((int)$selectedMushafPage, (int)$rule['value'], $juz);
                    } else {
                        $mushafPageOptions = $this->mushafPagesService->optionsForStage($juz, (float)$rule['value'], (int)$selectedMushafPage);
                    }
                    $lineStatus = $this->mushafLines->status();
                } catch (\Throwable $exception) {
                    report($exception);
                }
            }
        }

        return view('teacher.quran-journey.student', [
            'student'=>$student,
            'summary'=>$summary,
            'rules'=>$this->journey->stageRules(),
            'milestones'=>MemorizationMilestone::query()
                ->where('institution_id',$request->user()->institution_id)
                ->where('student_id',$student->id)
                ->with('retentionChecks')
                ->orderByRaw("CASE unit_type WHEN 'juz' THEN 1 WHEN 'foundation_five' THEN 2 WHEN 'fami_manzil' THEN 3 ELSE 4 END")
                ->latest('updated_at')->get(),
            'templates'=>QuranProgramTemplate::query()->with('steps')->where('status','active')->orderBy('duration_days')->get(),
            'enrollments'=>QuranProgramEnrollment::query()
                ->with(['template.steps','progress.step'])
                ->where('institution_id',$request->user()->institution_id)
                ->where('student_id',$student->id)
                ->latest()->get(),
            'heritageTerms'=>QuranHeritageTerm::query()->where('status','active')->orderBy('sort_order')->get(),
            'famiUnits'=>QuranDivisionUnit::query()->where('unit_type','fami_manzil')->orderBy('unit_number')->get(),
            'surahs'=>QuranSurah::query()->orderBy('sequence')->get(),
            'portions'=>QuranJourneyPortion::query()->with(['marhalah','startSurah','endSurah','targets.surah'])
                ->where('institution_id',$request->user()->institution_id)->where('student_id',$student->id)
                ->latest()->limit(30)->get(),
            'stageHistories'=>StudentMarhalahHistory::query()->with(['marhalahType','teacher'])
                ->where('student_id',$student->id)
                ->orderByDesc('effective_from')->orderByDesc('id')->get(),
            'mushafLineStatus'=>$lineStatus,
            'mushafPages'=>$mushafPages,
            'selectedMushafPage'=>$selectedMushafPage,
            'mushafLineBlocks'=>$mushafLineBlocks,
            'mushafPageOptions'=>$mushafPageOptions,
        ]);
    }

    public function initialize(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudent($request,$student);
        $teacher = $request->user()->teacher;
        abort_unless($teacher,403);
        $data = $request->validate([
            'current_juz_number'=>['required','integer','between:1,30'],
            'cadence_mode'=>['required',Rule::in(['flexible','daily','weekly','custom'])],
            'cadence_notes'=>['nullable','string','max:1000'],
            'reason'=>['nullable','string','max:2000'],
        ]);

        $this->journey->initializeProfile(
            $student,$teacher,(int)$data['current_juz_number'],$data['cadence_mode'],$data['cadence_notes'] ?? null,$data['reason'] ?? null,
        );
        return back()->with('success','Posisi awal Qur’an Journey tersimpan. Marhalah ditentukan otomatis dari Juz, bukan dipilih sebagai level kemampuan.');
    }

    public function updateCadence(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudent($request,$student);
        $teacher = $request->user()->teacher;
        abort_unless($teacher,403);
        $data = $request->validate([
            'cadence_mode'=>['required',Rule::in(['flexible','daily','weekly','custom'])],
            'cadence_notes'=>['nullable','string','max:1000'],
        ]);

        $this->journey->updateCadence($student,$teacher,$data['cadence_mode'],$data['cadence_notes'] ?? null);
        return back()->with('success','Arahan tahap aktif diperbarui. Catatan ini akan diarsipkan bersama Juz/Marhalah saat berpindah tahap.');
    }

    public function advance(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudent($request,$student);
        $teacher = $request->user()->teacher;
        abort_unless($teacher,403);
        $this->journey->advance($student,$teacher);
        return back()->with('success','Tahap Juz berikutnya dibuka dan Marhalah diperbarui otomatis sesuai metode Sullamul Ḥifẓ.');
    }

    public function storePortion(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudent($request,$student);
        $teacher = $request->user()->teacher;
        abort_unless($teacher,403);
        $data = $request->validate([
            'start_surah_id'=>['required','exists:quran_surahs,id'],
            'start_verse'=>['required','integer','min:1'],
            'end_surah_id'=>['required','exists:quran_surahs,id'],
            'end_verse'=>['required','integer','min:1'],
            'teacher_confirmed'=>['accepted'],
            'scheduled_for'=>['nullable','date'],
            'due_date'=>['nullable','date','after_or_equal:today'],
            'notes'=>['nullable','string','max:3000'],
        ]);
        $this->journey->createPortion(
            $student,$teacher,(int)$data['start_surah_id'],(int)$data['start_verse'],
            (int)$data['end_surah_id'],(int)$data['end_verse'],true,
            $data['scheduled_for'] ?? null,$data['due_date'] ?? null,$data['notes'] ?? null,
        );
        return back()->with('success','Porsi Marhalah dibuat. Jika rentangnya melewati pergantian surah, sistem memecah target setoran tanpa memecah makna satu porsi.');
    }

    public function storeLinePortion(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudent($request,$student);
        $teacher = $request->user()->teacher;
        abort_unless($teacher,403);
        $data = $request->validate([
            'page_number'=>['required','integer','between:1,604'],
            'start_line'=>['required','integer','between:1,15'],
            'block_size'=>['required','integer',Rule::in([3,5])],
            'scheduled_for'=>['nullable','date'],
            'due_date'=>['nullable','date','after_or_equal:today'],
            'notes'=>['nullable','string','max:3000'],
        ]);
        $portion = $this->mushafLines->createLinePortion(
            $student,$teacher,(int)$data['page_number'],(int)$data['start_line'],(int)$data['block_size'],
            $data['scheduled_for'] ?? null,$data['due_date'] ?? null,$data['notes'] ?? null,
        );
        return back()->with('success','Porsi Mushaf dibuat dari halaman '.$portion->start_page_number.' baris '.$portion->start_line_number.'–'.$portion->end_line_number.'. Target Tahfizh terhubung otomatis.');
    }

    public function storePagePortion(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudent($request,$student);
        $teacher = $request->user()->teacher;
        abort_unless($teacher,403);
        $data = $request->validate([
            'page_number'=>['required','integer','between:1,604'],
            'variant'=>['required',Rule::in(['half-top','half-bottom','page','two-pages'])],
            'scheduled_for'=>['nullable','date'],
            'due_date'=>['nullable','date','after_or_equal:today'],
            'notes'=>['nullable','string','max:3000'],
        ]);
        $portion = $this->mushafPagesService->createPagePortion(
            $student,$teacher,(int)$data['page_number'],$data['variant'],
            $data['scheduled_for'] ?? null,$data['due_date'] ?? null,$data['notes'] ?? null,
        );
        $range = $portion->start_page_number === $portion->end_page_number
            ? 'halaman '.$portion->start_page_number
            : 'halaman '.$portion->start_page_number.'–'.$portion->end_page_number;
        return back()->with('success','Porsi '.$portion->portion_label.' dibuat dari '.$range.' dan target Tahfizh terhubung otomatis.');
    }

    public function milestone(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudent($request,$student);
        $teacher = $request->user()->teacher;
        abort_unless($teacher,403);
        $data = $request->validate([
            'unit_type'=>['required',Rule::in(['surah','rubu','hizb','juz','fami_manzil'])],
            'unit_key'=>['required','string','max:100'],
            'label'=>['nullable','string','max:190'],
            'memorization_status'=>['required',Rule::in(['not_started','in_progress','completed'])],
            'notes'=>['nullable','string','max:3000'],
        ]);
        $this->journey->updateMilestone($student,$teacher,$data['unit_type'],$data['unit_key'],$data['label'] ?? null,$data['memorization_status'],$data['notes'] ?? null);
        return back()->with('success','Milestone hafalan diperbarui. Status penjagaan tetap dinilai terpisah.');
    }

    public function currentJuzMilestone(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudent($request,$student);
        $teacher = $request->user()->teacher;
        abort_unless($teacher,403);
        $profile = $student->quranJourneyProfile()->firstOrFail();
        $data = $request->validate([
            'memorization_status'=>['required',Rule::in(['in_progress','completed'])],
            'notes'=>['nullable','string','max:3000'],
        ]);
        $juz = (int)$profile->current_juz_number;
        $this->journey->updateMilestone($student,$teacher,'juz',(string)$juz,'Juz '.$juz,$data['memorization_status'],$data['notes'] ?? null);
        return back()->with('success',$data['memorization_status']==='completed' ? 'Juz ditandai selesai hafalan. Anda dapat membuka tahap berikutnya.' : 'Status Juz diperbarui.');
    }

    public function retention(Request $request, Student $student, MemorizationMilestone $milestone): RedirectResponse
    {
        $this->authorizeStudent($request,$student);
        abort_unless((int)$milestone->student_id===(int)$student->id && (int)$milestone->institution_id===(int)$request->user()->institution_id,404);
        $teacher = $request->user()->teacher;
        abort_unless($teacher,403);
        $data = $request->validate([
            'result'=>['required',Rule::in(['maintained','strengthening_needed','reactivation_needed'])],
            'assistance_level'=>['required',Rule::in(['none','little','several','much'])],
            'next_check_date'=>['nullable','date','after_or_equal:today'],
            'notes'=>['nullable','string','max:3000'],
        ]);
        $this->journey->recordRetention($milestone,$student,$teacher,$data['result'],$data['assistance_level'],$data['notes'] ?? null,$data['next_check_date'] ?? null);
        return back()->with('success','Pemeriksaan penjagaan tersimpan sebagai histori, tidak menimpa catatan sebelumnya.');
    }

    public function assignProgram(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudent($request,$student);
        $teacher = $request->user()->teacher;
        abort_unless($teacher,403);
        $data = $request->validate([
            'quran_program_template_id'=>['required','exists:quran_program_templates,id'],
            'purpose'=>['required',Rule::in(['tilawah','murajaah','both'])],
            'schedule_mode'=>['required',Rule::in(['daily','flexible'])],
            'start_date'=>['required','date'],
            'notes'=>['nullable','string','max:2000'],
        ]);
        $template = QuranProgramTemplate::query()->where('status','active')->findOrFail($data['quran_program_template_id']);
        $this->programs->startForStudent($student,$teacher,$template,$data['purpose'],$data['schedule_mode'],$data['start_date'],$data['notes'] ?? null);
        return back()->with('success','Program Qur’an santri dimulai. Jadwal adalah panduan; hari terlewat tidak diberi label gagal.');
    }

    public function programStep(Request $request, Student $student, QuranProgramEnrollment $enrollment): RedirectResponse
    {
        $this->authorizeStudent($request,$student);
        abort_unless((int)$enrollment->student_id===(int)$student->id && (int)$enrollment->institution_id===(int)$request->user()->institution_id,404);
        $data = $request->validate([
            'step_id'=>['required','exists:quran_program_steps,id'],
            'status'=>['required',Rule::in(['pending','in_progress','completed'])],
            'notes'=>['nullable','string','max:1000'],
        ]);
        $this->programs->markStep($enrollment,(int)$data['step_id'],$data['status'],$data['notes'] ?? null);
        return back()->with('success','Progress program Qur’an diperbarui.');
    }

    private function studentIds(Request $request): Collection
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher,403);
        $assignments = TeacherAssignment::query()
            ->where('institution_id',$request->user()->institution_id)
            ->where('teacher_id',$teacher->id)
            ->where('status','active')
            ->where(fn($q)=>$q->whereNull('valid_from')->orWhereDate('valid_from','<=',today()))
            ->where(fn($q)=>$q->whereNull('valid_until')->orWhereDate('valid_until','>=',today()))
            ->get();
        $classIds = $assignments->pluck('class_id')->filter();
        $groupIds = $assignments->pluck('learning_group_id')->filter();
        $fromClasses = ClassEnrollment::query()->whereIn('class_id',$classIds)->where('status','active')->pluck('student_id');
        $fromGroups = GroupMembership::query()->whereIn('learning_group_id',$groupIds)->where('status','active')->pluck('student_id');
        return $fromClasses->merge($fromGroups)->unique()->values();
    }

    private function authorizeStudent(Request $request, Student $student): void
    {
        abort_unless((int)$student->institution_id===(int)$request->user()->institution_id && $this->studentIds($request)->contains($student->id),403);
    }
}
