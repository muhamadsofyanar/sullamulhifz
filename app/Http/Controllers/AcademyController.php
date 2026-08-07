<?php

namespace App\Http\Controllers;

use App\Models\AcademyLesson;
use App\Models\AcademyLessonProgress;
use App\Models\AcademyModule;
use App\Models\AcademyProgram;
use App\Models\AcademyRecommendation;
use App\Models\QuranAudioSource;
use App\Models\QuranPracticePreset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AcademyController extends Controller
{
    public function index(Request $request): View
    {
        $programs = $this->programsFor($request);
        $completedIds = $this->completedLessonIds($request, $programs);
        $recommendations = $this->recommendationsFor($request, 6);
        $allLessons = $programs->flatMap(fn (AcademyProgram $program) => $program->modules->flatMap->lessons);
        $startedCount = AcademyLessonProgress::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('academy_lesson_id', $allLessons->pluck('id'))
            ->count();

        return view('academy.index', array_merge(
            compact('programs', 'completedIds', 'recommendations', 'allLessons', 'startedCount'),
            $this->viewContext($request),
        ));
    }

    public function programs(Request $request): View
    {
        $programs = $this->programsFor($request);
        $completedIds = $this->completedLessonIds($request, $programs);

        return view('academy.programs', array_merge(
            compact('programs', 'completedIds'),
            $this->viewContext($request),
        ));
    }

    public function classes(Request $request): View
    {
        $programs = $this->programsFor($request);
        $completedIds = $this->completedLessonIds($request, $programs);
        $progressRows = AcademyLessonProgress::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('academy_lesson_id', $programs->flatMap(fn ($program) => $program->modules->flatMap->lessons)->pluck('id'))
            ->get()
            ->keyBy('academy_lesson_id');

        return view('academy.classes', array_merge(
            compact('programs', 'completedIds', 'progressRows'),
            $this->viewContext($request),
        ));
    }

    public function modules(Request $request): View
    {
        $programs = $this->programsFor($request);
        $modules = $programs->flatMap(fn (AcademyProgram $program) => $program->modules->map(function (AcademyModule $module) use ($program) {
            $module->setRelation('program', $program);
            return $module;
        }));

        return view('academy.modules', array_merge(
            compact('programs', 'modules'),
            $this->viewContext($request),
        ));
    }

    public function materials(Request $request): View
    {
        return $this->catalog($request, 'Semua Materi', 'Materi', null, 'materials');
    }

    public function videos(Request $request): View
    {
        return $this->catalog($request, 'Video Academy', 'Video', ['video'], 'videos');
    }

    public function audio(Request $request): View
    {
        $view = $this->catalog($request, 'Audio & Latihan Al-Qur’an', 'Audio', ['audio'], 'audio', false);
        $data = $view->getData();
        $institutionId = (int) $request->user()->institution_id;
        $data['quranSources'] = QuranAudioSource::query()
            ->where('institution_id', $institutionId)
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->limit(6)
            ->get();
        $data['quranPresets'] = QuranPracticePreset::query()
            ->with(['source', 'startSurah', 'endSurah'])
            ->where('institution_id', $institutionId)
            ->where('status', 'active')
            ->whereIn('audience', $this->audiencesFor($request))
            ->orderByDesc('is_featured')
            ->limit(8)
            ->get();
        $data['quranPracticeUrl'] = rtrim((string) config('sullam.portal_base_url'), '/').'/latihan-quran';

        return view('academy.catalog', $data);
    }

    public function articles(Request $request): View
    {
        return $this->catalog($request, 'Artikel & Aktivitas', 'Artikel', ['article', 'activity', 'checklist'], 'articles');
    }

    public function progress(Request $request): View
    {
        $programs = $this->programsFor($request);
        $lessonIds = $programs->flatMap(fn ($program) => $program->modules->flatMap->lessons)->pluck('id');
        $rows = AcademyLessonProgress::query()
            ->with('lesson.module.program')
            ->where('user_id', $request->user()->id)
            ->whereIn('academy_lesson_id', $lessonIds)
            ->latest('updated_at')
            ->get();
        $completed = $rows->where('status', 'completed')->count();
        $total = $lessonIds->count();
        $percent = $total > 0 ? (int) round(($completed / $total) * 100) : 0;
        $programProgress = $programs->map(function (AcademyProgram $program) use ($rows): array {
            $ids = $program->modules->flatMap->lessons->pluck('id');
            $done = $rows->whereIn('academy_lesson_id', $ids)->where('status', 'completed')->count();
            $total = $ids->count();
            return [
                'program' => $program,
                'done' => $done,
                'total' => $total,
                'percent' => $total ? (int) round(($done / $total) * 100) : 0,
            ];
        });

        return view('academy.progress', array_merge(
            compact('programs', 'rows', 'completed', 'total', 'percent', 'programProgress'),
            $this->viewContext($request),
        ));
    }

    public function recommendations(Request $request): View
    {
        $recommendations = $this->recommendationsFor($request, 50);
        return view('academy.recommendations', array_merge(
            compact('recommendations'),
            $this->viewContext($request),
        ));
    }

    public function profile(Request $request): View
    {
        $programs = $this->programsFor($request);
        $lessonIds = $programs->flatMap(fn ($program) => $program->modules->flatMap->lessons)->pluck('id');
        $progress = AcademyLessonProgress::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('academy_lesson_id', $lessonIds)
            ->get();
        $user = $request->user()->load('roles', 'institution');
        $completed = $progress->where('status', 'completed')->count();
        $total = $lessonIds->count();
        $percent = $total ? (int) round(($completed / $total) * 100) : 0;

        return view('academy.profile', array_merge(
            compact('user', 'programs', 'completed', 'total', 'percent'),
            $this->viewContext($request),
        ));
    }

    public function program(Request $request, AcademyProgram $program): View
    {
        $this->authorizeProgram($request, $program);
        $program->load([
            'modules' => fn ($q) => $q->where('status', 'published')->orderBy('sort_order'),
            'modules.lessons' => fn ($q) => $q->where('status', 'published')->orderBy('sort_order'),
        ]);
        $lessonIds = $program->modules->flatMap->lessons->pluck('id');
        $progress = AcademyLessonProgress::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('academy_lesson_id', $lessonIds)
            ->get()
            ->keyBy('academy_lesson_id');

        return view('academy.program', array_merge(
            compact('program', 'progress'),
            $this->viewContext($request),
        ));
    }

    public function lesson(Request $request, AcademyLesson $lesson): View
    {
        $lesson->load('module.program');
        $this->authorizeProgram($request, $lesson->module->program);
        abort_unless($lesson->status === 'published', 404);

        $progress = AcademyLessonProgress::firstOrCreate(
            ['user_id' => $request->user()->id, 'academy_lesson_id' => $lesson->id],
            ['institution_id' => $request->user()->institution_id, 'status' => 'started', 'progress_percent' => 10, 'started_at' => now()]
        );
        if (! $progress->started_at) {
            $progress->update(['started_at' => now(), 'status' => 'started', 'progress_percent' => max(10, (int) $progress->progress_percent)]);
        }

        $siblings = AcademyLesson::query()
            ->where('academy_module_id', $lesson->academy_module_id)
            ->where('status', 'published')
            ->orderBy('sort_order')->orderBy('id')->get();
        $position = max(0, $siblings->search(fn ($item) => $item->id === $lesson->id));
        $previous = $position > 0 ? $siblings[$position - 1] : null;
        $next = $position < $siblings->count() - 1 ? $siblings[$position + 1] : null;

        return view('academy.lesson', array_merge(
            compact('lesson', 'progress', 'previous', 'next'),
            $this->viewContext($request),
        ));
    }

    public function complete(Request $request, AcademyLesson $lesson): RedirectResponse
    {
        $lesson->load('module.program');
        $this->authorizeProgram($request, $lesson->module->program);

        AcademyLessonProgress::updateOrCreate(
            ['user_id' => $request->user()->id, 'academy_lesson_id' => $lesson->id],
            [
                'institution_id' => $request->user()->institution_id,
                'status' => 'completed',
                'progress_percent' => 100,
                'started_at' => now(),
                'completed_at' => now(),
            ]
        );

        if ($request->user()->hasRole('guardian') && $request->user()->guardian) {
            $studentIds = $request->user()->guardian->students()->pluck('students.id');
            AcademyRecommendation::query()
                ->whereIn('student_id', $studentIds)
                ->where('academy_lesson_id', $lesson->id)
                ->where('status', 'active')
                ->update(['status' => 'completed', 'completed_at' => now(), 'updated_at' => now()]);
        }

        return back()->with('success', 'Materi ditandai selesai. Progres Academy sudah diperbarui.');
    }

    private function catalog(Request $request, string $title, string $eyebrow, ?array $types, string $section, bool $render = true): View
    {
        $programs = $this->programsFor($request);
        $programIds = $programs->pluck('id');
        $lessons = AcademyLesson::query()
            ->with('module.program')
            ->where('status', 'published')
            ->whereHas('module.program', function (Builder $query) use ($programIds): void {
                $query->whereIn('id', $programIds);
            })
            ->when($types, fn (Builder $query) => $query->whereIn('lesson_type', $types))
            ->orderByDesc('updated_at')
            ->get();

        $completedIds = AcademyLessonProgress::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'completed')
            ->whereIn('academy_lesson_id', $lessons->pluck('id'))
            ->pluck('academy_lesson_id');

        $data = array_merge(
            compact('title', 'eyebrow', 'types', 'section', 'programs', 'lessons', 'completedIds'),
            $this->viewContext($request),
        );

        return view('academy.catalog', $data);
    }

    private function programsFor(Request $request): Collection
    {
        return AcademyProgram::query()
            ->with([
                'modules' => fn ($q) => $q->where('status', 'published')->orderBy('sort_order'),
                'modules.lessons' => fn ($q) => $q->where('status', 'published')->orderBy('sort_order'),
            ])
            ->where('institution_id', $request->user()->institution_id)
            ->where('status', 'published')
            ->whereIn('audience', $this->audiencesFor($request))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    private function completedLessonIds(Request $request, Collection $programs): Collection
    {
        $lessonIds = $programs->flatMap(fn ($program) => $program->modules->flatMap->lessons)->pluck('id');
        return AcademyLessonProgress::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'completed')
            ->whereIn('academy_lesson_id', $lessonIds)
            ->pluck('academy_lesson_id');
    }

    private function recommendationsFor(Request $request, int $limit): Collection
    {
        $user = $request->user();
        if (! $user->hasRole('guardian') || ! $user->guardian) {
            return collect();
        }

        $studentIds = $user->guardian->students()->pluck('students.id');
        return AcademyRecommendation::query()
            ->with(['student', 'lesson.module.program'])
            ->where('institution_id', $user->institution_id)
            ->whereIn('student_id', $studentIds)
            ->whereIn('status', ['active', 'completed'])
            ->latest('recommended_at')
            ->limit($limit)
            ->get();
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
        abort_unless((int) $program->institution_id === (int) $request->user()->institution_id, 404);
        abort_unless($program->status === 'published' || $request->user()->hasAnyRole(['superadmin', 'institution_admin', 'head']), 404);
        abort_unless(in_array($program->audience, $this->audiencesFor($request), true), 403);
    }

    private function viewContext(Request $request): array
    {
        $academyHost = strtolower(trim((string) config('sullam.academy_host')));
        $standalone = $academyHost !== '' && strtolower($request->getHost()) === $academyHost;

        return [
            'academyStandalone' => $standalone,
            'academyLayout' => $standalone ? 'layouts.academy' : 'layouts.app',
            'academyRoutePrefix' => $standalone ? 'academy.portal.' : 'academy.',
        ];
    }
}
