<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuranProgramTemplate extends Model
{
    protected $fillable = ['code','name','program_type','duration_days','description','scholarly_note','status'];

    public function steps(): HasMany { return $this->hasMany(QuranProgramStep::class)->orderBy('sequence'); }
    public function enrollments(): HasMany { return $this->hasMany(QuranProgramEnrollment::class); }
}
