<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    use BelongsToInstitution;

    protected $fillable = [
        'institution_id', 'name', 'code', 'start_date', 'end_date', 'active_semester',
        'enrollment_status', 'status', 'is_active',
    ];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'is_active' => 'boolean'];
    }

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function periods(): HasMany { return $this->hasMany(AcademicPeriod::class); }
    public function classes(): HasMany { return $this->hasMany(SchoolClass::class); }
}
