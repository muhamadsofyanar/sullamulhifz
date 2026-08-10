<?php

namespace App\Models;

/** @phase 4.3 Identity & Relationship Core; @phase 4.5 workspace profile relation safeguards; @phase 5.0 billing relations; @phase 5.3 user preferences; @phase 6.0 infaq relation */

use App\Services\Communication\CommunicationService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'institution_id', 'name', 'email', 'phone', 'password', 'status',
        'last_login_at', 'last_login_ip', 'login_count', 'must_change_password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'login_count' => 'integer',
            'must_change_password' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }

    public function workspaceMemberships(): HasMany
    {
        return $this->hasMany(WorkspaceMembership::class);
    }

    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Institution::class, 'workspace_memberships')
            ->withPivot(['membership_type', 'display_label', 'status', 'is_default', 'joined_at', 'left_at', 'settings'])
            ->withTimestamps();
    }

    public function outgoingRelationships(): HasMany
    {
        return $this->hasMany(UserRelationship::class, 'from_user_id');
    }

    public function incomingRelationships(): HasMany
    {
        return $this->hasMany(UserRelationship::class, 'to_user_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot(['institution_id', 'branch_id', 'status', 'valid_from', 'valid_until'])
            ->withTimestamps();
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class)->where('institution_id', $this->institution_id);
    }

    public function guardian(): HasOne
    {
        return $this->hasOne(Guardian::class)->where('institution_id', $this->institution_id);
    }

    public function personalProfile(): HasOne
    {
        // personal_profiles.user_id is globally unique, so this relation does not
        // need a dynamic workspace predicate and remains safe for eager loading.
        return $this->hasOne(PersonalProfile::class);
    }
    public function personalModuleEnrollments(): HasMany { return $this->hasMany(PersonalModuleEnrollment::class); }
    public function accountInvitations(): HasMany { return $this->hasMany(AccountInvitation::class); }
    public function billingSubscriptions(): HasMany { return $this->hasMany(BillingSubscription::class); }
    public function billingInvoices(): HasMany { return $this->hasMany(BillingInvoice::class); }
    public function infaqTransactions(): HasMany { return $this->hasMany(InfaqTransaction::class); }
    public function preference(): HasOne { return $this->hasOne(UserPreference::class); }

    public function hasRole(string $role): bool
    {
        return $this->activeRolesQuery()->where('roles.name', $role)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->activeRolesQuery()->whereIn('roles.name', $roles)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->hasRole('superadmin')) {
            return true;
        }

        $roleIds = $this->activeRolesQuery()->pluck('roles.id');

        return Permission::query()
            ->where('permissions.name', $permission)
            ->whereHas('roles', fn ($query) => $query->whereIn('roles.id', $roleIds))
            ->exists();
    }

    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->hasRole('superadmin')) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function primaryRole(): ?string
    {
        $priority = ['superadmin', 'institution_admin', 'head', 'teacher', 'guardian', 'personal', 'student'];
        $active = $this->activeRolesQuery()->pluck('roles.name');

        return collect($priority)->first(fn (string $role): bool => $active->contains($role)) ?? $active->first();
    }

    public function isActiveMemberOf(int $institutionId): bool
    {
        return $this->workspaceMemberships()
            ->active()
            ->where('institution_id', $institutionId)
            ->exists();
    }

    public function sendPasswordResetNotification($token): void
    {
        $url = rtrim((string) config('sullam.portal_base_url'), '/')
            .'/atur-ulang-kata-sandi/'.$token
            .'?email='.rawurlencode((string) $this->getEmailForPasswordReset());

        try {
            $delivery = app(CommunicationService::class)->sendPasswordReset($this, $url);
            if ($delivery) {
                return;
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        $this->notify(new ResetPassword($token));
    }

    private function activeRolesQuery()
    {
        return $this->roles()
            ->wherePivot('status', 'active')
            ->where(function ($query): void {
                $query->whereNull('user_roles.institution_id');

                if ($this->institution_id) {
                    $query->orWhere('user_roles.institution_id', $this->institution_id);
                }
            })
            ->where(function ($query): void {
                $query->whereNull('user_roles.valid_from')
                    ->orWhere('user_roles.valid_from', '<=', today());
            })
            ->where(function ($query): void {
                $query->whereNull('user_roles.valid_until')
                    ->orWhere('user_roles.valid_until', '>=', today());
            });
    }
}
