<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'institution_id', 'name', 'email', 'phone', 'password', 'status',
        'last_login_at', 'last_login_ip', 'must_change_password',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'must_change_password' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot(['institution_id', 'status', 'valid_from', 'valid_until'])
            ->withTimestamps();
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    public function guardian(): HasOne
    {
        return $this->hasOne(Guardian::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->activeRolesQuery()->where('roles.name', $role)->exists();
    }

    public function hasAnyRole(array $roles): bool
    {
        return $this->activeRolesQuery()->whereIn('roles.name', $roles)->exists();
    }

    public function primaryRole(): ?string
    {
        return $this->activeRolesQuery()->value('roles.name');
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
