<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\FridayDevelopmentSession;
use App\Models\LearningGroup;
use App\Models\SchoolClass;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class ContentAudienceService
{
    public function announcementVisibleTo(Announcement $announcement, User $user): bool
    {
        if ((int) $announcement->institution_id !== (int) $user->institution_id) {
            return false;
        }

        if (! in_array($announcement->status, ['published', 'scheduled'], true)) {
            return $user->hasAnyRole(['superadmin', 'institution_admin', 'head']);
        }

        if ($announcement->publish_at && $announcement->publish_at->isFuture()) {
            return $user->hasAnyRole(['superadmin', 'institution_admin', 'head']);
        }

        if ($announcement->expires_at && $announcement->expires_at->isPast()) {
            return $user->hasAnyRole(['superadmin', 'institution_admin', 'head']);
        }

        if (Schema::hasTable('announcement_targets') && $announcement->targets()->exists()) {
            $targets = $announcement->targets;
            $classIds = $this->classIds($user);
            $groupIds = $this->groupIds($user);
            $roleIds = $user->roles()->wherePivot('status', 'active')->pluck('roles.id')->map(fn ($id) => (int) $id);
            $levelIds = SchoolClass::whereIn('id', $classIds)->pluck('level_id')->map(fn ($id) => (int) $id);

            return $targets->contains(function ($target) use ($user, $classIds, $groupIds, $roleIds, $levelIds): bool {
                return match ($target->target_type) {
                    'institution', 'all' => true,
                    'admins' => $user->hasAnyRole(['superadmin', 'institution_admin', 'head']),
                    'teachers' => $user->hasRole('teacher'),
                    'guardians' => $user->hasRole('guardian'),
                    'class' => $target->target_id && $classIds->contains((int) $target->target_id),
                    'group' => $target->target_id && $groupIds->contains((int) $target->target_id),
                    'level' => $target->target_id && $levelIds->contains((int) $target->target_id),
                    'user' => (int) $target->target_id === (int) $user->id,
                    'role' => $target->target_id && $roleIds->contains((int) $target->target_id),
                    default => false,
                };
            });
        }

        $audience = $announcement->audience_type ?: 'all';

        return match ($audience) {
            'all' => true,
            'admins' => $user->hasAnyRole(['superadmin', 'institution_admin', 'head']),
            'teachers' => $user->hasRole('teacher'),
            'guardians' => $user->hasRole('guardian'),
            'class' => $announcement->class_id && $this->classIds($user)->contains((int) $announcement->class_id),
            'group' => $announcement->learning_group_id && $this->groupIds($user)->contains((int) $announcement->learning_group_id),
            default => false,
        };
    }

    public function fridayVisibleTo(FridayDevelopmentSession $session, User $user): bool
    {
        if ((int) $session->institution_id !== (int) $user->institution_id) {
            return false;
        }

        if ($session->status !== 'published') {
            return $user->hasAnyRole(['superadmin', 'institution_admin', 'head']);
        }

        if (Schema::hasTable('friday_session_targets') && $session->targets()->exists()) {
            $classIds = $this->classIds($user);
            $groupIds = $this->groupIds($user);
            $levelIds = SchoolClass::whereIn('id', $classIds)->pluck('level_id');

            return $session->targets->contains(fn ($target): bool =>
                $target->target_all
                || ($target->class_id && $classIds->contains((int) $target->class_id))
                || ($target->learning_group_id && $groupIds->contains((int) $target->learning_group_id))
                || ($target->level_id && $levelIds->contains((int) $target->level_id))
            );
        }

        return $session->class_id === null || $this->classIds($user)->contains((int) $session->class_id);
    }

    public function classIds(User $user): Collection
    {
        if ($user->hasAnyRole(['superadmin', 'institution_admin', 'head'])) {
            return SchoolClass::where('institution_id', $user->institution_id)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values();
        }

        $ids = collect();

        if ($user->hasRole('guardian') && $user->guardian) {
            $ids = $ids->merge(
                $user->guardian->students()
                    ->with('currentEnrollment')
                    ->get()
                    ->pluck('currentEnrollment.class_id')
            );
        }

        if ($user->hasRole('teacher') && $user->teacher) {
            $ids = $ids->merge(
                TeacherAssignment::where('teacher_id', $user->teacher->id)
                    ->where('institution_id', $user->institution_id)
                    ->where('status', 'active')
                    ->where(function ($query): void {
                        $query->whereNull('valid_from')->orWhereDate('valid_from', '<=', today());
                    })
                    ->where(function ($query): void {
                        $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', today());
                    })
                    ->pluck('class_id')
            );
        }

        return $ids->filter()->map(fn ($id) => (int) $id)->unique()->values();
    }

    public function groupIds(User $user): Collection
    {
        if ($user->hasAnyRole(['superadmin', 'institution_admin', 'head'])) {
            return LearningGroup::where('institution_id', $user->institution_id)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values();
        }

        $ids = collect();

        if ($user->hasRole('guardian') && $user->guardian) {
            $ids = $ids->merge(
                $user->guardian->students()
                    ->with('groupMemberships')
                    ->get()
                    ->flatMap(fn ($student) => $student->groupMemberships
                        ->where('status', 'active')
                        ->pluck('learning_group_id'))
            );
        }

        if ($user->hasRole('teacher') && $user->teacher) {
            $ids = $ids->merge(
                TeacherAssignment::where('teacher_id', $user->teacher->id)
                    ->where('institution_id', $user->institution_id)
                    ->where('status', 'active')
                    ->where(function ($query): void {
                        $query->whereNull('valid_from')->orWhereDate('valid_from', '<=', today());
                    })
                    ->where(function ($query): void {
                        $query->whereNull('valid_until')->orWhereDate('valid_until', '>=', today());
                    })
                    ->pluck('learning_group_id')
            );
        }

        return $ids->filter()->map(fn ($id) => (int) $id)->unique()->values();
    }

}
