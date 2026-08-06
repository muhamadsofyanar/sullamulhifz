<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use SoftDeletes;
    protected $fillable = ['institution_id','academic_year_id','created_by_teacher_id','class_id','learning_group_id','title','assignment_type','target_text','surah_id','quran_audio_source_id','start_verse','end_verse','repeat_count','repeat_mode','learning_method','instructions','evidence_types','assigned_at','due_at','allow_resubmission','status'];
    protected function casts(): array { return ['evidence_types'=>'array','repeat_count'=>'integer','assigned_at'=>'datetime','due_at'=>'datetime','allow_resubmission'=>'boolean']; }
    public function recipients(): HasMany { return $this->hasMany(AssignmentRecipient::class); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function learningGroup(): BelongsTo { return $this->belongsTo(LearningGroup::class); }
    public function teacher(): BelongsTo { return $this->belongsTo(Teacher::class, 'created_by_teacher_id'); }
    public function audioSource(): BelongsTo { return $this->belongsTo(QuranAudioSource::class, 'quran_audio_source_id'); }
    public function surah(): BelongsTo { return $this->belongsTo(QuranSurah::class, 'surah_id'); }
}
