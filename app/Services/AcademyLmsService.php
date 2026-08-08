<?php

namespace App\Services;

use App\Models\AcademyCertificate;
use App\Models\AcademyLearningPath;
use App\Models\AcademyLesson;
use App\Models\AcademyLessonProgress;
use App\Models\AcademyPrerequisite;
use App\Models\AcademyProgram;
use App\Models\AcademyQuizAttempt;
use App\Models\AcademyWorksheetSubmission;
use App\Models\QuranPracticeSession;
use App\Models\User;
use Illuminate\Support\Str;

class AcademyLmsService
{
    public function isUnlocked(User $user, string $subjectType, int $subjectId): bool
    {
        if ($user->hasAnyRole(['superadmin', 'institution_admin', 'head'])) {
            return true;
        }

        return $this->missingPrerequisites($user, $subjectType, $subjectId) === [];
    }

    /** @return array<int, string> */
    public function missingPrerequisites(User $user, string $subjectType, int $subjectId): array
    {
        return AcademyPrerequisite::query()
            ->where('institution_id', $user->institution_id)
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->get()
            ->reject(fn (AcademyPrerequisite $item): bool => $this->requirementComplete($user, $item))
            ->map(fn (AcademyPrerequisite $item): string => $this->requirementLabel($item))
            ->values()
            ->all();
    }

    public function lessonRequirementsComplete(User $user, AcademyLesson $lesson): bool
    {
        $quiz = $lesson->quiz()->where('status', 'published')->first();
        if ($quiz && ! AcademyQuizAttempt::query()->where('user_id', $user->id)->where('academy_quiz_id', $quiz->id)->where('passed', true)->exists()) {
            return false;
        }

        $worksheet = $lesson->worksheet()->where('status', 'published')->where('is_required', true)->first();
        if ($worksheet && ! AcademyWorksheetSubmission::query()->where('user_id', $user->id)->where('academy_worksheet_id', $worksheet->id)->where('status', 'completed')->exists()) {
            return false;
        }

        return true;
    }

    public function issueCertificateIfEligible(User $user, AcademyProgram $program): ?AcademyCertificate
    {
        $existing = AcademyCertificate::query()->where('user_id', $user->id)->where('academy_program_id', $program->id)->first();
        if ($existing) {
            return $existing;
        }

        $program->loadMissing(['modules' => fn ($q) => $q->where('status', 'published'), 'modules.lessons' => fn ($q) => $q->where('status', 'published')]);
        $lessonIds = $program->modules->flatMap->lessons->pluck('id');
        if ($lessonIds->isEmpty()) {
            return null;
        }

        $completed = AcademyLessonProgress::query()
            ->where('user_id', $user->id)
            ->whereIn('academy_lesson_id', $lessonIds)
            ->where('status', 'completed')
            ->count();
        if ($completed !== $lessonIds->count()) {
            return null;
        }

        return AcademyCertificate::create([
            'institution_id' => $user->institution_id,
            'user_id' => $user->id,
            'academy_program_id' => $program->id,
            'certificate_number' => sprintf('SH-%d-%d-%d-%s', $user->institution_id, $program->id, $user->id, now()->format('Ymd')),
            'verification_code' => Str::lower((string) Str::uuid()),
            'status' => 'issued',
            'issued_at' => now(),
        ]);
    }

    public function pathComplete(User $user, AcademyLearningPath $path): bool
    {
        $items = $path->relationLoaded('items') ? $path->items : $path->items()->get();
        foreach ($items->where('is_required', true) as $item) {
            if ($item->item_type === 'lesson') {
                if (! AcademyLessonProgress::query()->where('user_id', $user->id)->where('academy_lesson_id', $item->item_id)->where('status', 'completed')->exists()) return false;
            } elseif ($item->item_type === 'quran_preset') {
                if (! QuranPracticeSession::query()->where('user_id', $user->id)->where('quran_practice_preset_id', $item->item_id)->where('status', 'completed')->exists()) return false;
            }
        }
        return true;
    }

    private function requirementComplete(User $user, AcademyPrerequisite $item): bool
    {
        if ($item->required_type === 'lesson') {
            return AcademyLessonProgress::query()->where('user_id', $user->id)->where('academy_lesson_id', $item->required_id)->where('status', 'completed')->exists();
        }
        if ($item->required_type === 'path') {
            $path = AcademyLearningPath::query()->where('institution_id', $user->institution_id)->find($item->required_id);
            return $path ? $this->pathComplete($user, $path) : false;
        }
        return false;
    }

    private function requirementLabel(AcademyPrerequisite $item): string
    {
        if ($item->required_type === 'lesson') return AcademyLesson::query()->find($item->required_id)?->title ?? 'Materi prasyarat';
        if ($item->required_type === 'path') return AcademyLearningPath::query()->find($item->required_id)?->title ?? 'Jalur belajar prasyarat';
        return 'Prasyarat';
    }
}
