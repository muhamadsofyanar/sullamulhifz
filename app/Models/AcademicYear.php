<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicYear extends Model
{
    protected $fillable = ['institution_id', 'name', 'code', 'start_date', 'end_date', 'active_semester', 'enrollment_status', 'status', 'is_active'];
    protected function casts(): array { return ['start_date'=>'date','end_date'=>'date','is_active'=>'boolean']; }
    public function classes(): HasMany { return $this->hasMany(SchoolClass::class); }
}
