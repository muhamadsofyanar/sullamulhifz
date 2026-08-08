<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademyLesson;
use App\Models\AcademyLessonProgress;
use App\Models\AcademyLearningPath;
use App\Models\AcademyLearningPathItem;
use App\Models\AcademyModule;
use App\Models\AcademyProgram;
use App\Models\AcademyPrerequisite;
use App\Models\AcademyQuiz;
use App\Models\AcademyQuizQuestion;
use App\Models\AcademyRecommendation;
use App\Models\AcademyWorksheet;
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
            'prerequisites' => AcademyPrerequisite::query()->where('institution_id', $institutionId)->latest()->get(),
            'quizzes' => AcademyQuiz::query()->with('lesson.module.program','questions.options')->whereHas('lesson.module.program', fn ($q) => $q->where('institution_id', $institutionId))->get(),
            'worksheets' => AcademyWorksheet::query()->with('lesson.module.program')->whereHas('lesson.module.program', fn ($q) => $q->where('institution_id', $institutionId))->get(),
            'academyLessons' => $programs->flatMap(fn (AcademyProgram $program) => $program->modules->flatMap->lessons),
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

    public function storePrerequisite(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject_type' => ['required', Rule::in(['lesson','path'])],
            'subject_id' => ['required','integer'],
            'required_type' => ['required', Rule::in(['lesson','path'])],
            'required_id' => ['required','integer'],
        ]);
        abort_if($data['subject_type'] === $data['required_type'] && (int) $data['subject_id'] === (int) $data['required_id'], 422, 'Item tidak dapat menjadi prasyarat dirinya sendiri.');
        $this->academyResource($request, $data['subject_type'], (int) $data['subject_id']);
        $this->academyResource($request, $data['required_type'], (int) $data['required_id']);
        abort_if($this->createsPrerequisiteCycle((int) $request->user()->institution_id, $data['subject_type'], (int) $data['subject_id'], $data['required_type'], (int) $data['required_id']), 422, 'Prasyarat ini akan membuat siklus sehingga konten tidak pernah dapat dibuka.');
        AcademyPrerequisite::firstOrCreate([
            'institution_id' => $request->user()->institution_id,
            'subject_type' => $data['subject_type'], 'subject_id' => $data['subject_id'],
            'required_type' => $data['required_type'], 'required_id' => $data['required_id'],
        ]);
        return back()->with('success', 'Prasyarat Academy disimpan.');
    }

    public function destroyPrerequisite(Request $request, AcademyPrerequisite $prerequisite): RedirectResponse
    {
        abort_unless((int) $prerequisite->institution_id === (int) $request->user()->institution_id, 404);
        $prerequisite->delete();
        return back()->with('success', 'Prasyarat dihapus.');
    }

    public function storeQuiz(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'academy_lesson_id' => ['required','integer','exists:academy_lessons,id'],
            'title' => ['required','string','max:180'],
            'instructions' => ['nullable','string','max:3000'],
            'passing_percent' => ['required','integer','min:1','max:100'],
            'max_attempts' => ['required','integer','min:1','max:10'],
            'status' => ['required', Rule::in(['draft','published'])],
        ]);
        $lesson = $this->academyResource($request, 'lesson', (int) $data['academy_lesson_id']);
        AcademyQuiz::updateOrCreate(['academy_lesson_id' => $lesson->id], $data);
        return back()->with('success', 'Kuis materi disimpan. Tambahkan minimal satu pertanyaan sebelum digunakan.');
    }

    public function storeQuizQuestion(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'academy_quiz_id' => ['required','integer','exists:academy_quizzes,id'],
            'prompt' => ['required','string','max:3000'],
            'points' => ['required','integer','min:1','max:100'],
            'explanation' => ['nullable','string','max:3000'],
            'options' => ['required','array','min:2','max:6'],
            'options.*' => ['required','string','max:1000'],
            'correct_option' => ['required','integer','min:0','max:5'],
        ]);
        $quiz = AcademyQuiz::with('lesson.module.program')->findOrFail($data['academy_quiz_id']);
        $this->own($request, $quiz->lesson->module->program);
        abort_unless(array_key_exists((int) $data['correct_option'], $data['options']), 422, 'Jawaban benar harus menunjuk salah satu opsi.');
        $question = $quiz->questions()->create([
            'question_type' => 'multiple_choice', 'prompt' => $data['prompt'], 'points' => $data['points'],
            'sort_order' => (int) $quiz->questions()->max('sort_order') + 1, 'explanation' => $data['explanation'] ?? null,
        ]);
        foreach (array_values($data['options']) as $index => $label) {
            $question->options()->create(['label' => $label, 'is_correct' => $index === (int) $data['correct_option'], 'sort_order' => $index + 1]);
        }
        return back()->with('success', 'Pertanyaan kuis ditambahkan.');
    }

    public function destroyQuizQuestion(Request $request, AcademyQuizQuestion $question): RedirectResponse
    {
        $question->load('quiz.lesson.module.program');
        $this->own($request, $question->quiz->lesson->module->program);
        $question->delete();
        return back()->with('success', 'Pertanyaan kuis dihapus.');
    }

    public function storeWorksheet(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'academy_lesson_id' => ['required','integer','exists:academy_lessons,id'],
            'title' => ['required','string','max:180'],
            'instructions' => ['nullable','string','max:5000'],
            'completion_mode' => ['required', Rule::in(['reflection','self_check'])],
            'is_required' => ['nullable','boolean'],
            'status' => ['required', Rule::in(['draft','published'])],
        ]);
        $lesson = $this->academyResource($request, 'lesson', (int) $data['academy_lesson_id']);
        $data['is_required'] = $request->boolean('is_required');
        AcademyWorksheet::updateOrCreate(['academy_lesson_id' => $lesson->id], $data);
        return back()->with('success', 'Worksheet materi disimpan.');
    }

    private function academyResource(Request $request, string $type, int $id): AcademyLesson|AcademyLearningPath
    {
        if ($type === 'lesson') {
            $lesson = AcademyLesson::with('module.program')->findOrFail($id);
            $this->own($request, $lesson->module->program);
            return $lesson;
        }
        $path = AcademyLearningPath::findOrFail($id);
        abort_unless((int) $path->institution_id === (int) $request->user()->institution_id, 404);
        return $path;
    }

    private function createsPrerequisiteCycle(int $institutionId, string $subjectType, int $subjectId, string $requiredType, int $requiredId): bool
    {
        $queue = [[$requiredType, $requiredId]];
        $seen = [];
        while ($queue !== []) {
            [$type, $id] = array_shift($queue);
            $key = $type.':'.$id;
            if (isset($seen[$key])) continue;
            $seen[$key] = true;
            if ($type === $subjectType && $id === $subjectId) return true;
            $dependencies = AcademyPrerequisite::query()->where('institution_id', $institutionId)->where('subject_type', $type)->where('subject_id', $id)->get();
            foreach ($dependencies as $dependency) $queue[] = [$dependency->required_type, (int) $dependency->required_id];
        }
        return false;
    }

    private function own(Request $request, AcademyProgram $program): void { abort_unless((int)$program->institution_id===(int)$request->user()->institution_id,404); }
    private function uniqueProgramSlug(Request $request,string $base): string { $slug=$base?:'program';$i=2;while(AcademyProgram::where('institution_id',$request->user()->institution_id)->where('slug',$slug)->exists()){$slug=$base.'-'.$i++;}return $slug; }
}
