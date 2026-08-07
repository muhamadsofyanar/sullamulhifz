<?php

namespace App\Http\Controllers;

use App\Models\AcademyLesson;
use App\Models\AcademyLessonProgress;
use App\Models\AcademyProgram;
use App\Models\AcademyRecommendation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademyController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $programs = AcademyProgram::query()
            ->with(['modules' => fn ($q) => $q->where('status','published'), 'modules.lessons' => fn ($q) => $q->where('status','published')])
            ->where('institution_id', $user->institution_id)
            ->where('status', 'published')
            ->whereIn('audience', $this->audiencesFor($request))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->get();

        $lessonIds = $programs->flatMap(fn ($program) => $program->modules->flatMap->lessons)->pluck('id');
        $completedIds = AcademyLessonProgress::query()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereIn('academy_lesson_id', $lessonIds)
            ->pluck('academy_lesson_id');

        $recommendations = collect();
        if ($user->hasRole('guardian') && $user->guardian) {
            $studentIds = $user->guardian->students()->pluck('students.id');
            $recommendations = AcademyRecommendation::query()
                ->with(['student','lesson.module.program'])
                ->where('institution_id', $user->institution_id)
                ->whereIn('student_id', $studentIds)
                ->where('status', 'active')
                ->latest('recommended_at')
                ->limit(8)
                ->get();
        }

        return view('academy.index', compact('programs','completedIds','recommendations'));
    }

    public function program(Request $request, AcademyProgram $program): View
    {
        $this->authorizeProgram($request, $program);
        $program->load(['modules' => fn ($q) => $q->where('status','published')->orderBy('sort_order'), 'modules.lessons' => fn ($q) => $q->where('status','published')->orderBy('sort_order')]);
        $lessonIds = $program->modules->flatMap->lessons->pluck('id');
        $progress = AcademyLessonProgress::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('academy_lesson_id', $lessonIds)
            ->get()
            ->keyBy('academy_lesson_id');

        return view('academy.program', compact('program','progress'));
    }

    public function lesson(Request $request, AcademyLesson $lesson): View
    {
        $lesson->load('module.program');
        $this->authorizeProgram($request, $lesson->module->program);
        abort_unless($lesson->status === 'published', 404);

        $progress = AcademyLessonProgress::firstOrCreate(
            ['user_id'=>$request->user()->id,'academy_lesson_id'=>$lesson->id],
            ['institution_id'=>$request->user()->institution_id,'status'=>'started','progress_percent'=>10,'started_at'=>now()]
        );
        if (! $progress->started_at) {
            $progress->update(['started_at'=>now(),'status'=>'started','progress_percent'=>max(10,(int)$progress->progress_percent)]);
        }

        $siblings = AcademyLesson::query()
            ->where('academy_module_id', $lesson->academy_module_id)
            ->where('status','published')
            ->orderBy('sort_order')->orderBy('id')->get();
        $position = max(0, $siblings->search(fn ($item) => $item->id === $lesson->id));
        $previous = $position > 0 ? $siblings[$position - 1] : null;
        $next = $position < $siblings->count() - 1 ? $siblings[$position + 1] : null;

        return view('academy.lesson', compact('lesson','progress','previous','next'));
    }

    public function complete(Request $request, AcademyLesson $lesson): RedirectResponse
    {
        $lesson->load('module.program');
        $this->authorizeProgram($request, $lesson->module->program);

        AcademyLessonProgress::updateOrCreate(
            ['user_id'=>$request->user()->id,'academy_lesson_id'=>$lesson->id],
            [
                'institution_id'=>$request->user()->institution_id,
                'status'=>'completed',
                'progress_percent'=>100,
                'started_at'=>now(),
                'completed_at'=>now(),
            ]
        );

        if ($request->user()->hasRole('guardian') && $request->user()->guardian) {
            $studentIds = $request->user()->guardian->students()->pluck('students.id');
            AcademyRecommendation::query()
                ->whereIn('student_id',$studentIds)
                ->where('academy_lesson_id',$lesson->id)
                ->where('status','active')
                ->update(['status'=>'completed','completed_at'=>now(),'updated_at'=>now()]);
        }

        return back()->with('success','Materi ditandai selesai. Progress Academy sudah diperbarui.');
    }

    private function audiencesFor(Request $request): array
    {
        $user = $request->user();
        if ($user->hasAnyRole(['superadmin', 'institution_admin', 'head'])) {
            return ['all', 'guardian', 'teacher', 'admin'];
        }

        $audiences = ['all'];
        if ($user->hasRole('teacher')) {
            $audiences[] = 'teacher';
        }
        if ($user->hasRole('guardian')) {
            $audiences[] = 'guardian';
        }

        return array_values(array_unique($audiences));
    }

    private function authorizeProgram(Request $request, AcademyProgram $program): void
    {
        abort_unless((int)$program->institution_id === (int)$request->user()->institution_id, 404);
        abort_unless($program->status === 'published' || $request->user()->hasAnyRole(['superadmin','institution_admin','head']), 404);
        abort_unless(in_array($program->audience, $this->audiencesFor($request), true), 403);
    }
}
