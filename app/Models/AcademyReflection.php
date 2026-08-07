<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademyReflection extends Model
{
    protected $guarded = [];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function lesson(): BelongsTo { return $this->belongsTo(AcademyLesson::class, 'academy_lesson_id'); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
}
