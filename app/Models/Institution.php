<?php

namespace App\Models;

/** @phase 4.3 Identity Core; @phase 4.4 Multi-tenant Institution Foundation */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'code', 'slug', 'workspace_type', 'owner_user_id', 'privacy_mode',
        'institution_type', 'onboarding_status', 'registration_source', 'custom_domain',
        'brand_primary_color', 'brand_secondary_color', 'terminology', 'approved_at', 'approved_by_user_id',
        'legal_name', 'phone', 'email', 'address', 'timezone', 'status', 'settings',
    ];

    protected function casts(): array
    {
        return ['settings' => 'array', 'terminology' => 'array', 'approved_at' => 'datetime'];
    }

    public function users(): HasMany { return $this->hasMany(User::class); }
    public function students(): HasMany { return $this->hasMany(Student::class); }
    public function teachers(): HasMany { return $this->hasMany(Teacher::class); }
    public function academicYears(): HasMany { return $this->hasMany(AcademicYear::class); }
    public function branches(): HasMany { return $this->hasMany(Branch::class); }
    public function featureFlags(): HasMany { return $this->hasMany(FeatureFlag::class); }
    public function mediaAssets(): HasMany { return $this->hasMany(MediaAsset::class); }
    public function personalProfiles(): HasMany { return $this->hasMany(PersonalProfile::class); }
    public function integrationConnections(): HasMany { return $this->hasMany(IntegrationConnection::class); }
    public function communicationDeliveries(): HasMany { return $this->hasMany(CommunicationDelivery::class); }
    public function communicationTemplates(): HasMany { return $this->hasMany(CommunicationTemplate::class); }
    public function workspaceMemberships(): HasMany { return $this->hasMany(WorkspaceMembership::class); }
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_memberships')
            ->withPivot(['membership_type', 'status', 'is_default'])
            ->withTimestamps();
    }

    public function term(string $subject): string
    {
        $defaults = \App\Support\InstitutionType::terminology((string) ($this->institution_type ?: 'tpa'));

        return (string) data_get($this->terminology ?: [], $subject, $defaults[$subject] ?? $subject);
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings ?: [], $key, $default);
    }
}
