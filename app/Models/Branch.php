<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Branch extends Model
{
    use BelongsToInstitution, SoftDeletes;

    protected $fillable = ['institution_id', 'name', 'code', 'address', 'phone', 'status', 'is_main'];

    protected function casts(): array
    {
        return ['is_main' => 'boolean'];
    }

    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
    public function students(): HasMany { return $this->hasMany(Student::class); }
    public function schoolClasses(): HasMany { return $this->hasMany(SchoolClass::class); }
    public function learningGroups(): HasMany { return $this->hasMany(LearningGroup::class); }
}
