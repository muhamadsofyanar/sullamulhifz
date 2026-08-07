<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FridaySessionTarget extends Model
{
    protected $fillable = ['friday_development_session_id', 'class_id', 'learning_group_id', 'level_id', 'target_all'];

    protected function casts(): array { return ['target_all' => 'boolean']; }

    public function session(): BelongsTo { return $this->belongsTo(FridayDevelopmentSession::class, 'friday_development_session_id'); }
    public function schoolClass(): BelongsTo { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function learningGroup(): BelongsTo { return $this->belongsTo(LearningGroup::class); }
    public function level(): BelongsTo { return $this->belongsTo(Level::class); }
}
