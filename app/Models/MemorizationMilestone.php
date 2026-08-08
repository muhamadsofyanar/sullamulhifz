<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemorizationMilestone extends Model
{
    protected $fillable = [
        'institution_id','student_id','unit_type','unit_key','label','start_surah_id','end_surah_id',
        'start_global_number','end_global_number','memorization_status','retention_status','memorized_at',
        'maintained_at','verified_by_teacher_id','notes',
    ];

    protected function casts(): array
    {
        return ['memorized_at' => 'datetime', 'maintained_at' => 'datetime'];
    }

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function verifier(): BelongsTo { return $this->belongsTo(Teacher::class, 'verified_by_teacher_id'); }
    public function retentionChecks(): HasMany { return $this->hasMany(MemorizationRetentionCheck::class); }
}
