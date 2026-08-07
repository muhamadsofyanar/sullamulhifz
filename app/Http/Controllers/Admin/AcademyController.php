<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademyLesson;
use App\Models\AcademyLessonProgress;
use App\Models\AcademyModule;
use App\Models\AcademyProgram;
use App\Models\AcademyRecommendation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademyController extends Controller
{
    public function index(Request $request): View
    {
        $institutionId = $request->user()->institution_id;
        $programs = AcademyProgram::with(['modules.lessons'])->where('institution_id',$institutionId)->orderBy('sort_order')->get();
        return view('admin.academy.index', [
            'programs'=>$programs,
            'progressCount'=>AcademyLessonProgress::where('institution_id',$institutionId)->where('status','completed')->count(),
            'recommendationCount'=>AcademyRecommendation::where('institution_id',$institutionId)->where('status','active')->count(),
            'lessonCount'=>AcademyLesson::whereHas('module.program', fn($q)=>$q->where('institution_id',$institutionId))->count(),
        ]);
    }

    public function storeProgram(Request $request): RedirectResponse
    {
        $data=$request->validate([
            'title'=>['required','string','max:160'], 'audience'=>['required',Rule::in(['guardian','teacher','all'])],
            'summary'=>['nullable','string','max:1000'], 'description'=>['nullable','string','max:10000'],
            'status'=>['required',Rule::in(['draft','published','archived'])], 'is_featured'=>['nullable','boolean'],
        ]);
        $slug=$this->uniqueProgramSlug($request, Str::slug($data['title']));
        AcademyProgram::create([...$data,'institution_id'=>$request->user()->institution_id,'slug'=>$slug,'is_featured'=>$request->boolean('is_featured')]);
        return back()->with('success','Program Academy berhasil dibuat.');
    }

    public function updateProgram(Request $request, AcademyProgram $program): RedirectResponse
    {
        $this->own($request,$program);
        $data=$request->validate([
            'title'=>['required','string','max:160'], 'audience'=>['required',Rule::in(['guardian','teacher','all'])],
            'summary'=>['nullable','string','max:1000'], 'description'=>['nullable','string','max:10000'],
            'status'=>['required',Rule::in(['draft','published','archived'])], 'is_featured'=>['nullable','boolean'],
        ]);
        $program->update([...$data,'is_featured'=>$request->boolean('is_featured')]);
        return back()->with('success','Program Academy diperbarui.');
    }

    public function storeModule(Request $request): RedirectResponse
    {
        $data=$request->validate(['academy_program_id'=>['required','integer','exists:academy_programs,id'],'title'=>['required','string','max:160'],'summary'=>['nullable','string','max:1000']]);
        $program=AcademyProgram::findOrFail($data['academy_program_id']); $this->own($request,$program);
        AcademyModule::create([...$data,'sort_order'=>(int)$program->modules()->max('sort_order')+1,'status'=>'published']);
        return back()->with('success','Modul Academy ditambahkan.');
    }

    public function storeLesson(Request $request): RedirectResponse
    {
        $data=$request->validate([
            'academy_module_id'=>['required','integer','exists:academy_modules,id'],'title'=>['required','string','max:180'],
            'lesson_type'=>['required',Rule::in(['article','video','audio','pdf','activity','checklist','link'])],
            'summary'=>['nullable','string','max:1000'],'body'=>['nullable','string','max:30000'],'media_url'=>['nullable','url','max:2000'],
            'duration_minutes'=>['nullable','integer','min:1','max:600'],'status'=>['required',Rule::in(['draft','published','archived'])],
            'requires_action'=>['nullable','boolean'],
        ]);
        $module=AcademyModule::with('program')->findOrFail($data['academy_module_id']); $this->own($request,$module->program);
        $base=Str::slug($data['title']); $slug=$base; $i=2;
        while(AcademyLesson::where('academy_module_id',$module->id)->where('slug',$slug)->exists()){$slug=$base.'-'.$i++;}
        AcademyLesson::create([...$data,'slug'=>$slug,'sort_order'=>(int)$module->lessons()->max('sort_order')+1,'requires_action'=>$request->boolean('requires_action')]);
        return back()->with('success','Materi Academy ditambahkan.');
    }

    public function updateLesson(Request $request, AcademyLesson $lesson): RedirectResponse
    {
        $lesson->load('module.program'); $this->own($request,$lesson->module->program);
        $data=$request->validate(['status'=>['required',Rule::in(['draft','published','archived'])],'title'=>['required','string','max:180'],'summary'=>['nullable','string','max:1000']]);
        $lesson->update($data);
        return back()->with('success','Materi Academy diperbarui.');
    }

    private function own(Request $request, AcademyProgram $program): void { abort_unless((int)$program->institution_id===(int)$request->user()->institution_id,404); }
    private function uniqueProgramSlug(Request $request,string $base): string { $slug=$base?:'program';$i=2;while(AcademyProgram::where('institution_id',$request->user()->institution_id)->where('slug',$slug)->exists()){$slug=$base.'-'.$i++;}return $slug; }
}
