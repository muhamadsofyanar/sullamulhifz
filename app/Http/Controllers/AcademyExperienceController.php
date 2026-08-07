<?php

namespace App\Http\Controllers;

use App\Models\AcademyBookmark;
use App\Models\AcademyLearningPath;
use App\Models\AcademyLesson;
use App\Models\AcademyLessonProgress;
use App\Models\AcademyReflection;
use App\Models\QuranAyah;
use App\Models\QuranPracticePreset;
use App\Models\QuranPracticeSession;
use App\Support\Feature;
use App\Services\RoadmapStatusService;
use App\Models\Institution;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class AcademyExperienceController extends Controller
{
    public function paths(Request $request): View
    {
        $paths = AcademyLearningPath::query()
            ->with('items')
            ->where('institution_id', $request->user()->institution_id)
            ->where('status', 'published')
            ->whereIn('audience', $this->audiencesFor($request))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->get();

        $progress = $paths->mapWithKeys(fn (AcademyLearningPath $path) => [$path->id => $this->pathProgress($request, $path)]);

        return view('academy.paths', compact('paths', 'progress'));
    }

    public function path(Request $request, AcademyLearningPath $path): View
    {
        $this->authorizePath($request, $path);
        $path->load('items');

        $lessonIds = $path->items->where('item_type', 'lesson')->pluck('item_id');
        $presetIds = $path->items->where('item_type', 'quran_preset')->pluck('item_id');
        $lessons = AcademyLesson::query()->with('module.program')->whereIn('id', $lessonIds)->get()->keyBy('id');
        $presets = QuranPracticePreset::query()
            ->where('institution_id', $request->user()->institution_id)
            ->whereIn('id', $presetIds)
            ->get()->keyBy('id');
        $completedLessons = AcademyLessonProgress::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'completed')
            ->whereIn('academy_lesson_id', $lessonIds)
            ->pluck('academy_lesson_id');
        $completedPresets = QuranPracticeSession::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'completed')
            ->whereIn('quran_practice_preset_id', $presetIds)
            ->pluck('quran_practice_preset_id')
            ->filter();

        return view('academy.path', [
            'path' => $path,
            'lessons' => $lessons,
            'presets' => $presets,
            'completedLessons' => $completedLessons,
            'completedPresets' => $completedPresets,
            'pathProgress' => $this->pathProgress($request, $path),
        ]);
    }

    public function bookmarks(Request $request): View
    {
        $rows = AcademyBookmark::query()
            ->where('institution_id', $request->user()->institution_id)
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        $lessonIds = $rows->where('bookmark_type', 'lesson')->pluck('bookmark_id');
        $presetIds = $rows->where('bookmark_type', 'quran_preset')->pluck('bookmark_id');
        $ayahGlobals = $rows->where('bookmark_type', 'quran_ayah')->pluck('bookmark_id');

        return view('academy.bookmarks', [
            'rows' => $rows,
            'lessons' => AcademyLesson::query()->with('module.program')->whereIn('id', $lessonIds)->get()->keyBy('id'),
            'presets' => QuranPracticePreset::query()->where('institution_id', $request->user()->institution_id)->whereIn('id', $presetIds)->get()->keyBy('id'),
            'ayahs' => QuranAyah::query()->with('surah')->whereIn('global_number', $ayahGlobals)->get()->keyBy('global_number'),
        ]);
    }

    public function toggleBookmark(Request $request, AcademyLesson $lesson): RedirectResponse
    {
        $lesson->load('module.program');
        $this->authorizeLesson($request, $lesson);

        $query = AcademyBookmark::query()
            ->where('user_id', $request->user()->id)
            ->where('bookmark_type', 'lesson')
            ->where('bookmark_id', $lesson->id);

        if ($query->exists()) {
            $query->delete();
            return back()->with('success', 'Materi dihapus dari daftar tersimpan.');
        }

        AcademyBookmark::create([
            'institution_id' => $request->user()->institution_id,
            'user_id' => $request->user()->id,
            'bookmark_type' => 'lesson',
            'bookmark_id' => $lesson->id,
            'label' => $lesson->title,
            'context' => ['program_id' => $lesson->module->program->id],
        ]);

        return back()->with('success', 'Materi disimpan untuk dibuka kembali nanti.');
    }


    public function togglePresetBookmark(Request $request, QuranPracticePreset $preset): RedirectResponse
    {
        abort_unless((int) $preset->institution_id === (int) $request->user()->institution_id, 404);
        abort_unless($preset->status === 'active', 404);
        abort_unless(in_array($preset->audience, $this->audiencesFor($request), true), 403);

        $query = AcademyBookmark::query()
            ->where('user_id', $request->user()->id)
            ->where('bookmark_type', 'quran_preset')
            ->where('bookmark_id', $preset->id);

        if ($query->exists()) {
            $query->delete();
            return back()->with('success', 'Preset latihan dihapus dari daftar tersimpan.');
        }

        AcademyBookmark::create([
            'institution_id' => $request->user()->institution_id,
            'user_id' => $request->user()->id,
            'bookmark_type' => 'quran_preset',
            'bookmark_id' => $preset->id,
            'label' => $preset->title,
            'context' => ['source_id' => $preset->quran_audio_source_id, 'mode' => $preset->mode],
        ]);

        return back()->with('success', 'Preset latihan disimpan untuk dipakai kembali.');
    }

    public function storeReflection(Request $request, AcademyLesson $lesson): RedirectResponse
    {
        $lesson->load('module.program');
        $this->authorizeLesson($request, $lesson);
        abort_unless(Feature::enabled('academy_reflections', (int) $request->user()->institution_id, true), 404);

        $data = $request->validate([
            'reflection' => ['required', 'string', 'max:3000'],
            'follow_up' => ['nullable', 'string', 'max:255'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
        ]);

        if (! empty($data['student_id'])) {
            $allowed = $request->user()->hasAnyRole(['superadmin', 'institution_admin', 'head'])
                || ($request->user()->hasRole('guardian') && $request->user()->guardian?->students()->whereKey($data['student_id'])->exists());
            abort_unless($allowed, 403);
        }

        AcademyReflection::create([
            'institution_id' => $request->user()->institution_id,
            'user_id' => $request->user()->id,
            'academy_lesson_id' => $lesson->id,
            'student_id' => $data['student_id'] ?? null,
            'reflection' => $data['reflection'],
            'follow_up' => $data['follow_up'] ?? null,
            'visibility' => 'private',
        ]);

        return back()->with('success', 'Refleksi tersimpan sebagai catatan pribadi Anda.');
    }

    public function ecosystem(Request $request): View
    {
        $institutionId = (int) $request->user()->institution_id;
        $institution = Institution::query()->findOrFail($institutionId);
        $phases = collect(app(RoadmapStatusService::class)->phases($institution));

        $enabled = \App\Models\FeatureFlag::query()
            ->where('institution_id', $institutionId)
            ->pluck('enabled', 'feature_key');

        return view('academy.ecosystem', compact('phases', 'enabled'));
    }

    private function pathProgress(Request $request, AcademyLearningPath $path): array
    {
        $items = $path->relationLoaded('items') ? $path->items : $path->items()->get();
        $required = $items->where('is_required', true);
        $lessonIds = $required->where('item_type', 'lesson')->pluck('item_id');
        $presetIds = $required->where('item_type', 'quran_preset')->pluck('item_id');

        $doneLessons = AcademyLessonProgress::query()
            ->where('user_id', $request->user()->id)->where('status', 'completed')->whereIn('academy_lesson_id', $lessonIds)->count();
        $donePresets = QuranPracticeSession::query()
            ->where('user_id', $request->user()->id)->where('status', 'completed')->whereIn('quran_practice_preset_id', $presetIds)->distinct('quran_practice_preset_id')->count('quran_practice_preset_id');
        $total = $required->count();
        $done = min($total, $doneLessons + $donePresets);

        return ['done' => $done, 'total' => $total, 'percent' => $total ? (int) round(($done / $total) * 100) : 0];
    }

    private function authorizePath(Request $request, AcademyLearningPath $path): void
    {
        abort_unless((int) $path->institution_id === (int) $request->user()->institution_id, 404);
        abort_unless($path->status === 'published', 404);
        abort_unless(in_array($path->audience, $this->audiencesFor($request), true), 403);
    }

    private function authorizeLesson(Request $request, AcademyLesson $lesson): void
    {
        $program = $lesson->module->program;
        abort_unless((int) $program->institution_id === (int) $request->user()->institution_id, 404);
        abort_unless($lesson->status === 'published' && $program->status === 'published', 404);
        abort_unless(in_array($program->audience, $this->audiencesFor($request), true), 403);
    }

    private function audiencesFor(Request $request): array
    {
        if ($request->user()->hasAnyRole(['superadmin', 'institution_admin', 'head'])) {
            return ['all', 'guardian', 'teacher', 'admin'];
        }
        $audiences = ['all'];
        if ($request->user()->hasRole('guardian')) $audiences[] = 'guardian';
        if ($request->user()->hasRole('teacher')) $audiences[] = 'teacher';
        return array_values(array_unique($audiences));
    }
}
