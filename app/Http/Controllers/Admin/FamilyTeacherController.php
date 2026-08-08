<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademyLesson;
use App\Models\FamilyLearningActivity;
use App\Models\TeacherCompetency;
use App\Models\TeacherCompetencyProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FamilyTeacherController extends Controller
{
    public function index(Request $request): View
    {
        $institutionId = (int) $request->user()->institution_id;
        return view('admin.family-teacher.index', [
            'competencies'=>TeacherCompetency::query()->with('lesson')->where('institution_id',$institutionId)->orderBy('sort_order')->orderBy('title')->get(),
            'progress'=>TeacherCompetencyProgress::query()->with(['teacher','competency','reviewedBy'])->where('institution_id',$institutionId)->latest('updated_at')->limit(50)->get(),
            'activities'=>FamilyLearningActivity::query()->with(['student','creator'])->where('institution_id',$institutionId)->latest()->limit(50)->get(),
            'teacherLessons'=>AcademyLesson::query()->with('module.program')->where('status','published')
                ->whereHas('module.program',fn ($q) => $q->where('institution_id',$institutionId)->where('status','published')->whereIn('audience',['teacher','all']))
                ->orderBy('title')->get(),
        ]);
    }

    public function storeCompetency(Request $request): RedirectResponse
    {
        $data = $this->validateCompetency($request);
        $institutionId = (int) $request->user()->institution_id;
        $this->validateLesson($institutionId, $data['academy_lesson_id'] ?? null);
        $base = Str::slug(($data['code'] ?? '') ?: $data['title'], '_') ?: 'kompetensi';
        $code = $base; $i = 2;
        while (TeacherCompetency::query()->where('institution_id',$institutionId)->where('code',$code)->exists()) $code = $base.'_'.$i++;
        TeacherCompetency::create([...$data,'institution_id'=>$institutionId,'code'=>$code,'sort_order'=>(int) TeacherCompetency::where('institution_id',$institutionId)->max('sort_order')+1]);
        return back()->with('success','Kompetensi guru ditambahkan tanpa skor atau ranking.');
    }

    public function updateCompetency(Request $request, TeacherCompetency $competency): RedirectResponse
    {
        $this->own($request,$competency);
        $data = $this->validateCompetency($request, false);
        $this->validateLesson((int) $request->user()->institution_id, $data['academy_lesson_id'] ?? null);
        unset($data['code']);
        $competency->update($data);
        return back()->with('success','Kompetensi guru diperbarui.');
    }

    public function reviewProgress(Request $request, TeacherCompetencyProgress $progress): RedirectResponse
    {
        abort_unless((int) $progress->institution_id === (int) $request->user()->institution_id, 404);
        abort_unless($progress->status === 'reflection_submitted', 422, 'Refleksi guru belum siap direview.');
        $data = $request->validate(['status'=>['required',Rule::in(['needs_follow_up','demonstrated'])],'review_note'=>['nullable','string','max:5000']]);
        $progress->update([...$data,'reviewed_by_user_id'=>$request->user()->id,'reviewed_at'=>now()]);
        return back()->with('success','Review kompetensi tersimpan. Status ini bukan nilai dan tidak digunakan untuk ranking.');
    }

    private function validateCompetency(Request $request, bool $withCode = true): array
    {
        $rules = [
            'title'=>['required','string','max:180'],
            'category'=>['required',Rule::in(['pedagogy','quran','family_communication','child_safeguarding','professional'])],
            'description'=>['nullable','string','max:5000'],
            'evidence_guidance'=>['nullable','string','max:5000'],
            'academy_lesson_id'=>['nullable','integer','exists:academy_lessons,id'],
            'status'=>['required',Rule::in(['active','archived'])],
        ];
        if ($withCode) $rules['code'] = ['nullable','string','max:60'];
        return $request->validate($rules);
    }

    private function validateLesson(int $institutionId, mixed $lessonId): void
    {
        if (! $lessonId) return;
        $lesson = AcademyLesson::with('module.program')->findOrFail($lessonId);
        abort_unless(
            (int) $lesson->module?->program?->institution_id === $institutionId
            && $lesson->status === 'published'
            && $lesson->module?->status === 'published'
            && $lesson->module?->program?->status === 'published'
            && in_array($lesson->module?->program?->audience,['teacher','all'],true),
            403
        );
    }

    private function own(Request $request, TeacherCompetency $competency): void
    {
        abort_unless((int) $competency->institution_id === (int) $request->user()->institution_id, 404);
    }
}
