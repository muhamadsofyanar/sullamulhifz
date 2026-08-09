<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TalentProgressRecord extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['recorded_on' => 'date']; }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function program(): BelongsTo { return $this->belongsTo(Program::class); }
    public function learningGroup(): BelongsTo { return $this->belongsTo(LearningGroup::class); }
    public function recorder(): BelongsTo { return $this->belongsTo(User::class, 'recorded_by_user_id'); }
}
