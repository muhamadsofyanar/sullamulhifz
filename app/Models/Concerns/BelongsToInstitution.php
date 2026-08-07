<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToInstitution
{
    public function scopeForInstitution(Builder $query, ?int $institutionId): Builder
    {
        return $query->where($query->qualifyColumn('institution_id'), $institutionId);
    }

    public function belongsToSameInstitutionAs(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasRole('superadmin') && $user->institution_id === null) {
            return true;
        }

        return (int) $this->getAttribute('institution_id') === (int) $user->institution_id;
    }

    public function abortUnlessSameInstitution(?User $user, int $status = 404): static
    {
        abort_unless($this->belongsToSameInstitutionAs($user), $status);

        return $this;
    }
}
