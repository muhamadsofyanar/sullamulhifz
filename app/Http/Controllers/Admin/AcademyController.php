<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademyLesson;
use App\Models\AcademyLessonProgress;
use App\Models\AcademyLearningPath;
use App\Models\AcademyLearningPathItem;
use App\Models\AcademyModule;
use App\Models\AcademyProgram;
use App\Models\AcademyRecommendation;
use App\Models\QuranPracticePreset;
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
        $programs = AcademyProgram::query()
            ->with(['modules.lessons'])
            ->where('institution_id', $institutionId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $lessonCount = $programs->sum(function (AcademyProgram $program): int {
            return $program->modules->sum(function (AcademyModule $module): int {
                return $module->lessons->count();
            });
        });

        return view('admin.academy.index', [
            'programs' => $programs,
            'progressCount' => AcademyLessonProgress::where('institution_id', $institutionId)->where('status', 'completed')->count(),
            'recommendationCount' => AcademyRecommendation::where('institution_id', $institutionId)->where('status', 'active')->count(),
            'lessonCount' => $lessonCount,
            'paths' => AcademyLearningPath::query()->with('items')->where('institution_id', $institutionId)->orderBy('sort_order')->orderBy('id')->get(),
            'quranPresets' => QuranPracticePreset::query()->where('institution_id', $institutionId)->where('status', 'active')->orderByDesc('is_featured')->orderBy('title')->get(),
        ]);
    }

    public function storeProgram(Request $request): RedirectResponse
    {
        $data=$request->validate([
            'title'=>['required','string','max:160'], 'audience'=>['required',Rule::in(['guardian','teacher','all'])],
            'category'=>['nullable','string','max:50'], 'learning_track'=>['nullable','string','max:50'],
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
            'category'=>['nullable','string','max:50'], 'learning_track'=>['nullable','string','max:50'],
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
            'lesson_type'=>['required',Rule::in(['article','video','audio','pdf','activity','checklist','reflection','link'])],
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
        $data = $request->validate([
            'title' => ['required','string','max:180'],
            'lesson_type' => ['required', Rule::in(['article','video','audio','pdf','activity','checklist','reflection','link'])],
            'summary' => ['nullable','string','max:1000'],
            'body' => ['nullable','string','max:30000'],
            'media_url' => ['nullable','url','max:2000'],
            'duration_minutes' => ['nullable','integer','min:1','max:600'],
            'status' => ['required', Rule::in(['draft','published','archived'])],
            'requires_action' => ['nullable','boolean'],
        ]);
        $data['requires_action'] = $request->boolean('requires_action');
        $lesson->update($data);
        return back()->with('success','Materi Academy diperbarui.');
    }


    public function storePath(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required','string','max:160'],
            'audience' => ['required', Rule::in(['guardian','teacher','all'])],
            'category' => ['nullable','string','max:50'],
            'summary' => ['nullable','string','max:1000'],
            'status' => ['required', Rule::in(['draft','published','archived'])],
            'is_featured' => ['nullable','boolean'],
        ]);
        $base = Str::slug($data['title']) ?: 'jalur-belajar';
        $slug = $base; $i = 2;
        while (AcademyLearningPath::query()->where('institution_id',$request->user()->institution_id)->where('slug',$slug)->exists()) {
            $slug = $base.'-'.$i++;
        }
        AcademyLearningPath::create([
            ...$data,
            'institution_id' => $request->user()->institution_id,
            'slug' => $slug,
            'is_featured' => $request->boolean('is_featured'),
            'sort_order' => (int) AcademyLearningPath::where('institution_id',$request->user()->institution_id)->max('sort_order') + 1,
        ]);
        return back()->with('success','Jalur belajar berhasil dibuat. Tambahkan materi atau preset Qur’an sebagai langkah berikutnya.');
    }

    public function updatePath(Request $request, AcademyLearningPath $path): RedirectResponse
    {
        abort_unless((int)$path->institution_id === (int)$request->user()->institution_id, 404);
        $data = $request->validate([
            'title' => ['required','string','max:160'],
            'audience' => ['required', Rule::in(['guardian','teacher','all'])],
            'category' => ['nullable','string','max:50'],
            'summary' => ['nullable','string','max:1000'],
            'status' => ['required', Rule::in(['draft','published','archived'])],
            'is_featured' => ['nullable','boolean'],
        ]);
        $path->update([...$data, 'is_featured' => $request->boolean('is_featured')]);
        return back()->with('success','Jalur belajar diperbarui.');
    }

    public function storePathItem(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'academy_learning_path_id' => ['required','integer','exists:academy_learning_paths,id'],
            'item_type' => ['required', Rule::in(['lesson','quran_preset'])],
            'item_id' => ['required','integer'],
            'title_override' => ['nullable','string','max:160'],
            'instruction' => ['nullable','string','max:1000'],
            'is_required' => ['nullable','boolean'],
        ]);
        $path = AcademyLearningPath::findOrFail($data['academy_learning_path_id']);
        abort_unless((int)$path->institution_id === (int)$request->user()->institution_id, 404);

        if ($data['item_type'] === 'lesson') {
            $lesson = AcademyLesson::with('module.program')->findOrFail($data['item_id']);
            abort_unless((int)$lesson->module->program->institution_id === (int)$request->user()->institution_id, 404);
        } else {
            $preset = QuranPracticePreset::where('institution_id',$request->user()->institution_id)->findOrFail($data['item_id']);
        }

        AcademyLearningPathItem::updateOrCreate(
            ['academy_learning_path_id'=>$path->id,'item_type'=>$data['item_type'],'item_id'=>$data['item_id']],
            [
                'title_override'=>$data['title_override'] ?? null,
                'instruction'=>$data['instruction'] ?? null,
                'is_required'=>$request->boolean('is_required'),
                'sort_order'=>(int)$path->items()->max('sort_order')+1,
            ]
        );
        return back()->with('success','Langkah jalur belajar berhasil ditambahkan.');
    }

    public function destroyPathItem(Request $request, AcademyLearningPathItem $item): RedirectResponse
    {
        $item->load('path');
        abort_unless((int)$item->path->institution_id === (int)$request->user()->institution_id, 404);
        $item->delete();
        return back()->with('success','Langkah dihapus dari jalur belajar.');
    }

    private function own(Request $request, AcademyProgram $program): void { abort_unless((int)$program->institution_id===(int)$request->user()->institution_id,404); }
    private function uniqueProgramSlug(Request $request,string $base): string { $slug=$base?:'program';$i=2;while(AcademyProgram::where('institution_id',$request->user()->institution_id)->where('slug',$slug)->exists()){$slug=$base.'-'.$i++;}return $slug; }
}
