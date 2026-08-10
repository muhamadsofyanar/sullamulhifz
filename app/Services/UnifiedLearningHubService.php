<?php

namespace App\Services;

/** @phase 4.9 Learning & Academy Integration — unified Personal learning hub */

use App\Models\AcademyLessonProgress;
use App\Models\AcademyRecommendation;
use App\Models\AssignmentRecipient;
use App\Models\GuidedQuranEnrollment;
use App\Models\MentorshipSession;
use App\Models\PersonalGoal;
use App\Models\QuranPracticeSession;
use App\Models\QuranProgramEnrollment;
use App\Models\User;
use App\Models\WorkspaceMembership;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UnifiedLearningHubService
{
    public function __construct(private readonly PersonalModuleAccessService $modules) {}

    /** @return array<string,mixed> */
    public function snapshot(User $user): array
    {
        $profile = $user->personalProfile()->firstOrFail();
        $activeModules = $this->modules->activeModules($user);
        $workspaceIds = $this->activeWorkspaceIds($user);
        $academyEnabled = $activeModules->contains(fn (array $module): bool => $module['key'] === 'academy');

        $personalGoals = Schema::hasTable('personal_goals')
            ? PersonalGoal::query()
                ->where('user_id', $user->id)
                ->where('institution_id', $user->institution_id)
                ->where('status', 'active')
                ->orderByRaw('due_on is null, due_on asc')
                ->limit(6)
                ->get()
            : collect();

        $mentorSessions = Schema::hasTable('mentorship_sessions')
            ? MentorshipSession::query()
                ->with('mentor:id,name')
                ->where('learner_user_id', $user->id)
                ->whereHas('relationship', fn ($query) => $query->where('relationship_type', 'mentor_learner')->where('status', 'accepted'))
                ->whereIn('status', ['requested', 'scheduled'])
                ->orderByRaw('scheduled_at is null, scheduled_at asc')
                ->limit(5)
                ->get()
            : collect();

        $guidedEnrollments = Schema::hasTable('guided_quran_enrollments')
            ? GuidedQuranEnrollment::query()
                ->with(['program.provider', 'program.academyProgram', 'submissions' => fn ($query) => $query->latest('submitted_at')->limit(3)])
                ->where('learner_user_id', $user->id)
                ->where('learner_institution_id', $user->institution_id)
                ->whereIn('status', ['active', 'completed'])
                ->latest('enrolled_at')
                ->limit(6)
                ->get()
            : collect();

        $journeyEnrollments = Schema::hasTable('quran_program_enrollments')
            ? QuranProgramEnrollment::query()
                ->with(['template', 'progress.step'])
                ->where('institution_id', $user->institution_id)
                ->where(function ($query) use ($user, $profile): void {
                    $query->where('user_id', $user->id);
                    if ($profile->student_id) {
                        $query->orWhere('student_id', $profile->student_id);
                    }
                })
                ->whereIn('status', ['active', 'completed'])
                ->latest('updated_at')
                ->limit(6)
                ->get()
            : collect();

        $academyRecommendations = ($academyEnabled && $profile->student_id && Schema::hasTable('academy_recommendations'))
            ? AcademyRecommendation::query()
                ->with('lesson.module.program')
                ->where('student_id', $profile->student_id)
                ->whereIn('institution_id', $workspaceIds)
                ->where('status', 'active')
                ->latest('recommended_at')
                ->limit(6)
                ->get()
            : collect();

        $institutionAssignments = ($profile->student_id && Schema::hasTable('assignment_recipients'))
            ? AssignmentRecipient::query()
                ->with(['assignment.teacher'])
                ->where('student_id', $profile->student_id)
                ->whereIn('status', ['assigned', 'in_progress'])
                ->whereHas('assignment', fn ($query) => $query
                    ->whereIn('institution_id', $workspaceIds)
                    ->where('status', 'published'))
                ->latest('updated_at')
                ->limit(6)
                ->get()
            : collect();

        $practice = $this->practiceSummary($user);
        $academy = $this->academySummary($user, $guidedEnrollments, $academyRecommendations);
        $nextActions = $this->nextActions(
            $personalGoals,
            $mentorSessions,
            $guidedEnrollments,
            $journeyEnrollments,
            $academyRecommendations,
            $institutionAssignments,
            $activeModules,
        );

        return compact(
            'profile',
            'activeModules',
            'personalGoals',
            'mentorSessions',
            'guidedEnrollments',
            'journeyEnrollments',
            'academyRecommendations',
            'institutionAssignments',
            'practice',
            'academy',
            'nextActions',
        );
    }

    /** @return array<int,int> */
    private function activeWorkspaceIds(User $user): array
    {
        $ids = Schema::hasTable('workspace_memberships')
            ? WorkspaceMembership::query()
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->pluck('institution_id')
                ->map(fn ($id): int => (int) $id)
                ->all()
            : [];

        $ids[] = (int) $user->institution_id;
        return array_values(array_unique(array_filter($ids)));
    }

    /** @return array<string,int> */
    private function practiceSummary(User $user): array
    {
        if (! Schema::hasTable('quran_practice_sessions')) {
            return ['sessions_30d' => 0, 'minutes_30d' => 0, 'completed_repetitions_30d' => 0];
        }

        $rows = QuranPracticeSession::query()
            ->where('institution_id', $user->institution_id)
            ->where('user_id', $user->id)
            ->where('started_at', '>=', now()->subDays(30))
            ->get(['duration_seconds', 'repeat_completed', 'status']);

        return [
            'sessions_30d' => $rows->count(),
            'minutes_30d' => (int) round($rows->sum('duration_seconds') / 60),
            'completed_repetitions_30d' => (int) $rows->sum('repeat_completed'),
        ];
    }

    /** @param Collection<int,GuidedQuranEnrollment> $guidedEnrollments
     *  @param Collection<int,AcademyRecommendation> $recommendations
     *  @return array<string,int>
     */
    private function academySummary(User $user, Collection $guidedEnrollments, Collection $recommendations): array
    {
        if (! Schema::hasTable('academy_lesson_progress')) {
            return ['programs' => 0, 'started' => 0, 'completed' => 0, 'recommendations' => $recommendations->count()];
        }

        $programIds = $guidedEnrollments
            ->pluck('program.academy_program_id')
            ->filter()
            ->merge($recommendations->pluck('lesson.module.academy_program_id')->filter())
            ->unique()
            ->values();

        $lessonIds = collect();
        if ($programIds->isNotEmpty() && Schema::hasTable('academy_lessons') && Schema::hasTable('academy_modules')) {
            $lessonIds = DB::table('academy_lessons as l')
                ->join('academy_modules as m', 'm.id', '=', 'l.academy_module_id')
                ->whereIn('m.academy_program_id', $programIds)
                ->where('l.status', 'published')
                ->pluck('l.id');
        }

        $progress = $lessonIds->isNotEmpty()
            ? AcademyLessonProgress::query()
                ->where('user_id', $user->id)
                ->whereIn('academy_lesson_id', $lessonIds)
                ->get()
            : collect();

        return [
            'programs' => $programIds->count(),
            'started' => $progress->count(),
            'completed' => $progress->where('status', 'completed')->count(),
            'recommendations' => $recommendations->count(),
        ];
    }

    /**
     * @return Collection<int,array<string,mixed>>
     */
    private function nextActions(
        Collection $goals,
        Collection $mentorSessions,
        Collection $guidedEnrollments,
        Collection $journeyEnrollments,
        Collection $academyRecommendations,
        Collection $institutionAssignments,
        Collection $activeModules,
    ): Collection {
        $actions = collect();

        foreach ($goals->take(2) as $goal) {
            $actions->push([
                'source' => 'Target Personal',
                'title' => $goal->title,
                'meta' => $goal->due_on ? 'Target sampai '.$goal->due_on->translatedFormat('d M Y') : 'Target aktif tanpa tenggat',
                'route' => 'personal.dashboard',
                'fragment' => 'target',
                'priority' => 10,
            ]);
        }

        foreach ($mentorSessions->take(2) as $session) {
            $actions->push([
                'source' => 'Ustadz Privat',
                'title' => $session->focus,
                'meta' => $session->scheduled_at ? 'Dijadwalkan '.$session->scheduled_at->translatedFormat('d M Y H:i') : 'Menunggu penjadwalan sesi',
                'route' => 'mentorship.index',
                'priority' => 20,
            ]);
        }

        foreach ($institutionAssignments->take(2) as $recipient) {
            $actions->push([
                'source' => 'Lembaga',
                'title' => $recipient->assignment?->title ?? 'Tugas belajar',
                'meta' => $recipient->assignment?->due_at ? 'Tenggat '.$recipient->assignment->due_at->translatedFormat('d M Y H:i') : 'Tugas aktif dari lembaga',
                'route' => 'personal.dashboard',
                'priority' => 30,
            ]);
        }

        foreach ($guidedEnrollments->where('status', 'active')->take(2) as $enrollment) {
            $latest = $enrollment->submissions->first();
            $actions->push([
                'source' => 'Program Asatidz',
                'title' => $enrollment->program?->title ?? 'Program Al-Qur’an',
                'meta' => $latest && $latest->review_status === 'pending' ? 'Setoran terakhir sedang menunggu review' : 'Program aktif · lanjutkan setoran berikutnya',
                'route' => 'personal.learning.index',
                'priority' => 40,
            ]);
        }

        foreach ($academyRecommendations->take(2) as $recommendation) {
            $actions->push([
                'source' => 'Academy',
                'title' => $recommendation->lesson?->title ?? 'Materi rekomendasi',
                'meta' => $recommendation->message ?: 'Materi direkomendasikan untuk Anda',
                'route' => 'academy.lesson',
                'route_params' => ['lesson' => $recommendation->academy_lesson_id],
                'priority' => 50,
            ]);
        }

        foreach ($journeyEnrollments->where('status', 'active')->take(1) as $enrollment) {
            $actions->push([
                'source' => 'Qur’an Journey',
                'title' => $enrollment->template?->name ?? 'Perjalanan Qur’an',
                'meta' => 'Langkah aktif '.$enrollment->current_step,
                'route' => 'quran-journey.index',
                'priority' => 60,
            ]);
        }

        if ($activeModules->contains(fn (array $module): bool => $module['key'] === 'quran_practice')) {
            $actions->push([
                'source' => 'Latihan Qur’an',
                'title' => 'Jaga satu sesi latihan hari ini',
                'meta' => 'Latihan mandiri tetap menjadi jejak privat Anda',
                'route' => 'quran-practice.index',
                'priority' => 70,
            ]);
        }

        return $actions->sortBy('priority')->take(8)->values();
    }
}
