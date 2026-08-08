<?php

namespace App\Http\Controllers;

use App\Models\QuranDivisionUnit;
use App\Models\QuranHeritageTerm;
use App\Models\QuranProgramEnrollment;
use App\Models\QuranProgramTemplate;
use App\Services\QuranProgramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuranJourneyController extends Controller
{
    public function __construct(private readonly QuranProgramService $programs)
    {
    }

    public function index(Request $request): View
    {
        return view('quran-journey.index', $this->viewData($request));
    }

    public function academy(Request $request): View
    {
        return view('quran-journey.academy', $this->viewData($request));
    }

    /** @return array<string,mixed> */
    private function viewData(Request $request): array
    {
        return [
            'templates'=>QuranProgramTemplate::query()->with('steps')->where('status','active')->orderBy('duration_days')->get(),
            'enrollments'=>QuranProgramEnrollment::query()
                ->with(['template.steps','progress.step'])
                ->where('institution_id',$request->user()->institution_id)
                ->where('user_id',$request->user()->id)
                ->latest()->get(),
            'heritageTerms'=>QuranHeritageTerm::query()->where('status','active')->orderBy('sort_order')->get(),
            'divisionCounts'=>QuranDivisionUnit::query()->selectRaw('unit_type, COUNT(*) total')->groupBy('unit_type')->pluck('total','unit_type'),
            'famiUnits'=>QuranDivisionUnit::query()->where('unit_type','fami_manzil')->orderBy('unit_number')->get(),
        ];
    }

    public function start(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'quran_program_template_id'=>['required','exists:quran_program_templates,id'],
            'purpose'=>['required',Rule::in(['tilawah','murajaah','both'])],
            'schedule_mode'=>['required',Rule::in(['daily','flexible'])],
            'start_date'=>['required','date'],
            'notes'=>['nullable','string','max:2000'],
        ]);
        $template = QuranProgramTemplate::query()->where('status','active')->findOrFail($data['quran_program_template_id']);
        $this->programs->startForUser((int)$request->user()->institution_id,$request->user(),$template,$data['purpose'],$data['schedule_mode'],$data['start_date'],$data['notes'] ?? null);
        return back()->with('success','Program Qur’an dimulai. Fokusnya kesinambungan, bukan menghukum hari yang terlewat.');
    }

    public function step(Request $request, QuranProgramEnrollment $enrollment): RedirectResponse
    {
        abort_unless((int)$enrollment->institution_id===(int)$request->user()->institution_id && (int)$enrollment->user_id===(int)$request->user()->id,404);
        $data = $request->validate([
            'step_id'=>['required','exists:quran_program_steps,id'],
            'status'=>['required',Rule::in(['pending','in_progress','completed'])],
            'notes'=>['nullable','string','max:1000'],
        ]);
        $this->programs->markStep($enrollment,(int)$data['step_id'],$data['status'],$data['notes'] ?? null);
        return back()->with('success','Progress program diperbarui.');
    }
}
