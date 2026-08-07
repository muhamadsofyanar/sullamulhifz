<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademyModule extends Model
{
    protected $fillable = ['academy_program_id','title','summary','sort_order','status','metadata'];
    protected function casts(): array { return ['metadata' => 'array']; }
    public function program(): BelongsTo { return $this->belongsTo(AcademyProgram::class, 'academy_program_id'); }
    public function lessons(): HasMany { return $this->hasMany(AcademyLesson::class)->orderBy('sort_order')->orderBy('id'); }
}
