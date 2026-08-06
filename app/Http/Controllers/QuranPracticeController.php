<?php

namespace App\Http\Controllers;

use App\Models\ClassEnrollment;
use App\Models\GroupMembership;
use App\Models\MemorizationTarget;
use App\Models\QuranAudioSource;
use App\Models\QuranAyahTiming;
use App\Models\QuranPracticePreset;
use App\Models\QuranPracticeSession;
use App\Models\QuranRubu;
use App\Models\QuranSurah;
use App\Models\QuranVideoResource;
use App\Models\Student;
use App\Models\TeacherAssignment;
use App\Services\QuranPlaylistBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuranPracticeController extends Controller
{
    public function index(Request $request): View
    {
        $institutionId = (int) $request->user()->institution_id;
        $sources = QuranAudioSource::query()
            ->where('institution_id', $institutionId)
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->get();

        $presets = QuranPracticePreset::query()
            ->with(['source', 'rubu', 'startSurah', 'endSurah'])
            ->where('institution_id', $institutionId)
            ->where('status', 'active')
            ->when($request->user()->hasRole('teacher'), fn ($query) => $query->whereIn('audience', ['all', 'teacher']))
            ->when($request->user()->hasRole('guardian'), fn ($query) => $query->whereIn('audience', ['all', 'guardian']))
            ->orderByDesc('is_featured')
            ->orderBy('title')
            ->get();

        $students = $this->availableStudents($request);
        $defaultSource = $sources->firstWhere('is_default', true) ?: $sources->first();

        return view('quran-practice.index', [
            'sources' => $sources,
            'defaultSource' => $defaultSource,
            'presets' => $presets,
            'featuredPresets' => $presets->where('is_featured', true)->values(),
            'videos' => QuranVideoResource::query()
                ->with('surah')
                ->where('institution_id', $institutionId)
                ->where('status', 'published')
                ->latest()
                ->get(),
            'students' => $students,
            'targets' => MemorizationTarget::query()
                ->with(['student', 'surah', 'rubu', 'marhalah'])
                ->where('institution_id', $institutionId)
                ->whereIn('student_id', $students->pluck('id'))
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->latest()
                ->limit(50)
                ->get(),
            'surahs' => QuranSurah::query()->whereBetween('id', [78, 114])->orderByDesc('id')->get(),
            'rubus' => QuranRubu::query()->where('juz_number', 30)->where('status', 'active')->orderBy('rubu_number')->get(),
            'pages' => $defaultSource
                ? QuranAyahTiming::query()
                    ->where('quran_audio_source_id', $defaultSource->id)
                    ->whereNotNull('page_number')
                    ->distinct()
                    ->orderBy('page_number')
                    ->pluck('page_number')
                : collect(),
            'timingCount' => $defaultSource
                ? QuranAyahTiming::query()->where('quran_audio_source_id', $defaultSource->id)->whereBetween('surah_id', [78, 114])->count()
                : 0,
        ]);
    }

    public function playlist(Request $request, QuranPlaylistBuilder $builder): JsonResponse
    {
        $data = $request->validate([
            'preset_id' => ['nullable', 'integer', 'exists:quran_practice_presets,id'],
            'target_id' => ['nullable', 'integer', 'exists:memorization_targets,id'],
            'source_id' => ['nullable', 'integer', 'exists:quran_audio_sources,id'],
            'mode' => ['nullable', Rule::in(['ayah', 'range', 'surah', 'page', 'rubu'])],
            'rubu_id' => ['nullable', 'integer', 'exists:quran_rubus,id'],
            'page_number' => ['nullable', 'integer', 'min:1', 'max:604'],
            'surah_id' => ['nullable', 'integer', 'exists:quran_surahs,id'],
            'start_verse' => ['nullable', 'integer', 'min:1'],
            'end_verse' => ['nullable', 'integer', 'min:1'],
            'repeat_count' => ['nullable', 'integer', 'min:0', 'max:100'],
            'repeat_scope' => ['nullable', Rule::in(['each_item', 'whole_selection'])],
            'gap_seconds' => ['nullable', 'integer', 'min:0', 'max:15'],
            'playback_rate' => ['nullable', 'numeric', 'min:0.65', 'max:1.5'],
        ]);

        $institutionId = (int) $request->user()->institution_id;

        if (! empty($data['preset_id'])) {
            $preset = QuranPracticePreset::query()->where('institution_id', $institutionId)->findOrFail($data['preset_id']);
            $payload = $builder->fromPreset($preset);
        } elseif (! empty($data['target_id'])) {
            $target = MemorizationTarget::query()->with('surah')->where('institution_id', $institutionId)->findOrFail($data['target_id']);
            $this->authorizeTarget($request, $target);
            $payload = $builder->fromTarget($target, isset($data['source_id']) ? (int) $data['source_id'] : null);
        } else {
            $payload = $builder->build([
                'institution_id' => $institutionId,
                'source_id' => $data['source_id'] ?? null,
                'mode' => $data['mode'] ?? 'range',
                'rubu_id' => $data['rubu_id'] ?? null,
                'page_number' => $data['page_number'] ?? null,
                'start_surah_id' => $data['surah_id'] ?? null,
                'end_surah_id' => $data['surah_id'] ?? null,
                'start_verse' => $data['start_verse'] ?? 1,
                'end_verse' => $data['end_verse'] ?? ($data['start_verse'] ?? 1),
                'repeat_count' => $data['repeat_count'] ?? 3,
                'repeat_scope' => $data['repeat_scope'] ?? 'each_item',
                'gap_seconds' => $data['gap_seconds'] ?? 1,
                'playback_rate' => $data['playback_rate'] ?? 1,
                'title' => 'Latihan pilihan sendiri',
            ]);
        }

        return response()->json($payload);
    }

    public function target(Request $request, MemorizationTarget $target): RedirectResponse
    {
        abort_unless((int) $target->institution_id === (int) $request->user()->institution_id, 404);
        $this->authorizeTarget($request, $target);

        return redirect()->route('quran-practice.index', ['target' => $target->id]);
    }

    public function startSession(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'preset_id' => ['nullable', 'integer', 'exists:quran_practice_presets,id'],
            'mode' => ['required', Rule::in(['ayah', 'range', 'surah', 'page', 'rubu'])],
            'selection' => ['required', 'array'],
            'repeat_target' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        if (! empty($data['student_id'])) {
            $this->authorizeStudent($request, (int) $data['student_id']);
        }

        if (! empty($data['preset_id'])) {
            QuranPracticePreset::query()
                ->where('institution_id', $request->user()->institution_id)
                ->findOrFail($data['preset_id']);
        }

        $session = QuranPracticeSession::query()->create([
            'institution_id' => $request->user()->institution_id,
            'user_id' => $request->user()->id,
            'student_id' => $data['student_id'] ?? null,
            'quran_practice_preset_id' => $data['preset_id'] ?? null,
            'mode' => $data['mode'],
            'selection' => $data['selection'],
            'repeat_target' => $data['repeat_target'],
            'repeat_completed' => 0,
            'started_at' => now(),
            'status' => 'started',
        ]);

        return response()->json(['id' => $session->id]);
    }

    public function completeSession(Request $request, QuranPracticeSession $session): JsonResponse
    {
        abort_unless((int) $session->institution_id === (int) $request->user()->institution_id, 404);
        abort_unless((int) $session->user_id === (int) $request->user()->id, 403);

        $data = $request->validate([
            'repeat_completed' => ['required', 'integer', 'min:0', 'max:1000'],
            'duration_seconds' => ['required', 'integer', 'min:0', 'max:86400'],
            'status' => ['required', Rule::in(['completed', 'stopped'])],
        ]);

        $session->update([...$data, 'completed_at' => now()]);

        return response()->json(['saved' => true]);
    }

    private function availableStudents(Request $request): Collection
    {
        $user = $request->user();

        if ($user->hasAnyRole(['superadmin', 'institution_admin', 'head'])) {
            return Student::query()
                ->where('institution_id', $user->institution_id)
                ->where('status', 'active')
                ->orderBy('full_name')
                ->get();
        }

        if ($user->hasRole('guardian')) {
            return $user->guardian?->students()
                ->where('students.status', 'active')
                ->orderBy('students.full_name')
                ->get() ?? collect();
        }

        if ($user->hasRole('teacher')) {
            $teacher = $user->teacher;
            if (! $teacher) {
                return collect();
            }

            $assignments = TeacherAssignment::query()
                ->where('teacher_id', $teacher->id)
                ->where('status', 'active')
                ->get();

            $studentIds = ClassEnrollment::query()
                ->whereIn('class_id', $assignments->pluck('class_id')->filter())
                ->where('status', 'active')
                ->pluck('student_id')
                ->merge(
                    GroupMembership::query()
                        ->whereIn('learning_group_id', $assignments->pluck('learning_group_id')->filter())
                        ->where('status', 'active')
                        ->pluck('student_id')
                )
                ->unique();

            return Student::query()
                ->where('institution_id', $user->institution_id)
                ->whereIn('id', $studentIds)
                ->where('status', 'active')
                ->orderBy('full_name')
                ->get();
        }

        return collect();
    }

    private function authorizeTarget(Request $request, MemorizationTarget $target): void
    {
        if ($request->user()->hasAnyRole(['superadmin', 'institution_admin', 'head'])) {
            return;
        }

        $this->authorizeStudent($request, (int) $target->student_id);
    }

    private function authorizeStudent(Request $request, int $studentId): void
    {
        abort_unless($this->availableStudents($request)->contains('id', $studentId), 403);
    }
}
