<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'code', 'slug', 'workspace_type', 'owner_user_id', 'privacy_mode',
        'legal_name', 'phone', 'email', 'address', 'timezone', 'status', 'settings',
    ];

    protected function casts(): array
    {
        return ['settings' => 'array'];
    }

    public function users(): HasMany { return $this->hasMany(User::class); }
    public function students(): HasMany { return $this->hasMany(Student::class); }
    public function teachers(): HasMany { return $this->hasMany(Teacher::class); }
    public function academicYears(): HasMany { return $this->hasMany(AcademicYear::class); }
    public function branches(): HasMany { return $this->hasMany(Branch::class); }
    public function featureFlags(): HasMany { return $this->hasMany(FeatureFlag::class); }
    public function mediaAssets(): HasMany { return $this->hasMany(MediaAsset::class); }
    public function personalProfiles(): HasMany { return $this->hasMany(PersonalProfile::class); }

    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings ?: [], $key, $default);
    }
}
