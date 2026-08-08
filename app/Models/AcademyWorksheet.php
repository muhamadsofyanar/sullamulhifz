<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class AcademyWorksheet extends Model {
    protected $guarded = [];
    protected function casts(): array { return ['is_required' => 'boolean']; }
    public function lesson(): BelongsTo { return $this->belongsTo(AcademyLesson::class, 'academy_lesson_id'); }
    public function submissions(): HasMany { return $this->hasMany(AcademyWorksheetSubmission::class); }
}
