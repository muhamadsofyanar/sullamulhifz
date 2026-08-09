<?php

namespace App\Services;

use App\Models\GuidedQuranEnrollment;
use App\Models\PersonalModuleEnrollment;
use App\Models\QuranPracticeSession;
use App\Models\QuranProgramEnrollment;
use App\Models\User;
use App\Support\Feature;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class PersonalModuleAccessService
{
    /** @return array<string,array<string,mixed>> */
    public function definitions(): array
    {
        return [
            'quran_practice' => [
                'title' => 'Latihan Qur’an',
                'mobile_label' => 'Latihan',
                'eyebrow' => 'LATIHAN MANDIRI',
                'description' => 'Mushaf, murattal, pengulangan ayat, dan sesi latihan pribadi.',
                'route' => 'quran-practice.index',
                'route_pattern' => 'quran-practice.*',
                'icon' => 'listen',
                'feature' => 'quran_audio',
                'self_enrollable' => true,
            ],
            'quran_journey' => [
                'title' => 'Qur’an Journey',
                'mobile_label' => 'Journey',
                'eyebrow' => 'PROGRAM BERJALAN',
                'description' => 'Tilawah dan murāja‘ah dengan langkah program yang dapat dijaga bertahap.',
                'route' => 'quran-journey.index',
                'route_pattern' => 'quran-journey.*',
                'icon' => 'continuity',
                'feature' => 'quran_journey',
                'self_enrollable' => true,
            ],
            'guided_learning' => [
                'title' => 'Program dengan Asatidz',
                'mobile_label' => 'Asatidz',
                'eyebrow' => 'PENDAMPINGAN',
                'description' => 'Ikuti program, kirim setoran yang dipilih, lalu terima koreksi dari reviewer.',
                'route' => 'personal.learning.index',
                'route_pattern' => 'personal.learning.*',
                'icon' => 'growth',
                'feature' => null,
                'self_enrollable' => true,
            ],
            'academy' => [
                'title' => 'Academy',
                'mobile_label' => 'Academy',
                'eyebrow' => 'MATERI PROGRAM',
                'description' => 'Materi Academy yang terhubung dengan program yang sedang Anda ikuti.',
                'route' => 'academy.index',
                'route_pattern' => 'academy.*',
                'icon' => 'lesson',
                'feature' => 'academy_portal',
                'self_enrollable' => false,
            ],
        ];
    }

    /** @return array<string,bool> */
    public function accessMap(User $user): array
    {
        return collect(array_keys($this->definitions()))
            ->mapWithKeys(fn (string $key): array => [$key => $this->allows($user, $key)])
            ->all();
    }

    public function allows(User $user, string $moduleKey): bool
    {
        if (! $user->hasRole('personal')) {
            return true;
        }

        $definition = $this->definitions()[$moduleKey] ?? null;
        if (! $definition || ! $this->featureAvailable($user, $definition['feature'])) {
            return false;
        }

        if ($moduleKey !== 'academy') {
            $directStatus = $this->directEnrollmentStatus($user, $moduleKey);
            if ($directStatus !== null) {
                return $directStatus;
            }
        }

        return match ($moduleKey) {
            'guided_learning' => $this->hasGuidedEnrollment($user),
            'quran_journey' => $this->hasJourneyEnrollment($user),
            'quran_practice' => $this->hasPracticeHistory($user),
            'academy' => $this->hasAcademyProgram($user),
            default => false,
        };
    }

    /** @return Collection<int,array<string,mixed>> */
    public function activeModules(User $user): Collection
    {
        return collect($this->definitions())
            ->filter(fn (array $definition, string $key): bool => $this->allows($user, $key))
            ->map(fn (array $definition, string $key): array => [
                'key' => $key,
                ...$definition,
                'count' => $this->activityCount($user, $key),
            ])
            ->values();
    }

    /** @return Collection<int,array<string,mixed>> */
    public function registrationCatalog(): Collection
    {
        return collect($this->definitions())
            ->filter(fn (array $definition): bool => $definition['self_enrollable'])
            ->map(fn (array $definition, string $key): array => ['key' => $key, ...$definition])
            ->values();
    }

    /** @return Collection<int,array<string,mixed>> */
    public function catalog(User $user): Collection
    {
        return collect($this->definitions())
            ->filter(fn (array $definition): bool => $this->featureAvailable($user, $definition['feature']))
            ->map(fn (array $definition, string $key): array => [
                'key' => $key,
                ...$definition,
                'active' => $this->allows($user, $key),
                'can_deactivate' => $this->canDeactivate($user, $key),
                'count' => $this->activityCount($user, $key),
            ])
            ->values();
    }

    public function enroll(User $user, string $moduleKey, string $source = 'self'): PersonalModuleEnrollment
    {
        $definition = $this->definitions()[$moduleKey] ?? null;
        if (! $definition || ! $definition['self_enrollable']) {
            throw ValidationException::withMessages(['program' => 'Program ini hanya tersedia melalui program yang ditugaskan atau diikuti.']);
        }
        if (! $this->featureAvailable($user, $definition['feature'])) {
            throw ValidationException::withMessages(['program' => 'Program ini belum tersedia pada workspace Personal Anda.']);
        }

        $profile = $user->personalProfile()->firstOrFail();

        return PersonalModuleEnrollment::query()->updateOrCreate(
            ['personal_profile_id' => $profile->id, 'module_key' => $moduleKey],
            [
                'institution_id' => $user->institution_id,
                'user_id' => $user->id,
                'status' => 'active',
                'enrollment_source' => $source,
                'enrolled_at' => now(),
                'expires_at' => null,
            ],
        );
    }

    public function rememberDerivedEnrollment(User $user, string $moduleKey, string $source): void
    {
        if (! Schema::hasTable('personal_module_enrollments')) {
            return;
        }

        $profile = $user->personalProfile()->first();
        if (! $profile) {
            return;
        }

        PersonalModuleEnrollment::query()->updateOrCreate(
            ['personal_profile_id' => $profile->id, 'module_key' => $moduleKey],
            [
                'institution_id' => $user->institution_id,
                'user_id' => $user->id,
                'status' => 'active',
                'enrollment_source' => $source,
                'enrolled_at' => now(),
                'expires_at' => null,
            ],
        );
    }

    public function deactivate(User $user, string $moduleKey): PersonalModuleEnrollment
    {
        $definition = $this->definitions()[$moduleKey] ?? null;
        if (! $definition || ! $definition['self_enrollable']) {
            throw ValidationException::withMessages(['program' => 'Program ini tidak dapat dinonaktifkan secara mandiri.']);
        }
        if ($this->hasRequiredActiveProgram($user, $moduleKey)) {
            throw ValidationException::withMessages([
                'program' => 'Program masih terhubung dengan program aktif yang Anda ikuti. Selesaikan atau akhiri enrollment program tersebut terlebih dahulu.',
            ]);
        }

        $profile = $user->personalProfile()->firstOrFail();
        $enrollment = PersonalModuleEnrollment::query()
            ->where('personal_profile_id', $profile->id)
            ->where('module_key', $moduleKey)
            ->first();

        if (! $enrollment || $enrollment->status !== 'active') {
            throw ValidationException::withMessages(['program' => 'Program ini tidak sedang aktif di Ruang Personal Anda.']);
        }

        $enrollment->update(['status' => 'inactive', 'expires_at' => now()]);

        return $enrollment;
    }

    public function canDeactivate(User $user, string $moduleKey): bool
    {
        $definition = $this->definitions()[$moduleKey] ?? null;

        return (bool) $definition
            && $definition['self_enrollable']
            && $this->directEnrollmentStatus($user, $moduleKey) === true
            && ! $this->hasRequiredActiveProgram($user, $moduleKey);
    }

    private function directEnrollmentStatus(User $user, string $moduleKey): ?bool
    {
        if (! Schema::hasTable('personal_module_enrollments')) {
            return null;
        }

        $enrollment = PersonalModuleEnrollment::query()
            ->where('institution_id', $user->institution_id)
            ->where('user_id', $user->id)
            ->where('module_key', $moduleKey)
            ->first(['status', 'expires_at']);

        if (! $enrollment) {
            return null;
        }

        return $enrollment->status === 'active'
            && ($enrollment->expires_at === null || $enrollment->expires_at->isFuture());
    }

    private function hasGuidedEnrollment(User $user): bool
    {
        return Schema::hasTable('guided_quran_enrollments') && GuidedQuranEnrollment::query()
            ->where('learner_institution_id', $user->institution_id)
            ->where('learner_user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    private function hasJourneyEnrollment(User $user): bool
    {
        return Schema::hasTable('quran_program_enrollments') && QuranProgramEnrollment::query()
            ->where('institution_id', $user->institution_id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->exists();
    }

    private function hasPracticeHistory(User $user): bool
    {
        return Schema::hasTable('quran_practice_sessions') && QuranPracticeSession::query()
            ->where('institution_id', $user->institution_id)
            ->where('user_id', $user->id)
            ->exists();
    }

    private function hasAcademyProgram(User $user): bool
    {
        return Schema::hasTable('guided_quran_enrollments') && GuidedQuranEnrollment::query()
            ->where('learner_institution_id', $user->institution_id)
            ->where('learner_user_id', $user->id)
            ->where('status', 'active')
            ->whereHas('program', fn ($query) => $query->whereNotNull('academy_program_id'))
            ->exists();
    }

    private function hasRequiredActiveProgram(User $user, string $moduleKey): bool
    {
        return match ($moduleKey) {
            'guided_learning' => $this->hasGuidedEnrollment($user),
            'quran_journey' => $this->hasJourneyEnrollment($user),
            default => false,
        };
    }

    private function featureAvailable(User $user, ?string $feature): bool
    {
        return $feature === null || Feature::enabled($feature, (int) $user->institution_id, true);
    }

    private function activityCount(User $user, string $moduleKey): int
    {
        return match ($moduleKey) {
            'guided_learning' => Schema::hasTable('guided_quran_enrollments') ? GuidedQuranEnrollment::query()
                ->where('learner_institution_id', $user->institution_id)->where('learner_user_id', $user->id)->where('status', 'active')->count() : 0,
            'quran_journey' => Schema::hasTable('quran_program_enrollments') ? QuranProgramEnrollment::query()
                ->where('institution_id', $user->institution_id)->where('user_id', $user->id)->where('status', 'active')->count() : 0,
            'quran_practice' => Schema::hasTable('quran_practice_sessions') ? QuranPracticeSession::query()
                ->where('institution_id', $user->institution_id)->where('user_id', $user->id)->count() : 0,
            'academy' => Schema::hasTable('guided_quran_enrollments') ? GuidedQuranEnrollment::query()
                ->where('learner_institution_id', $user->institution_id)->where('learner_user_id', $user->id)->where('status', 'active')
                ->whereHas('program', fn ($query) => $query->whereNotNull('academy_program_id'))->count() : 0,
            default => 0,
        };
    }
}
