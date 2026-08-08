<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AcademyWorksheetSubmission extends Model {
    protected $guarded = [];
    protected function casts(): array { return ['completed_at' => 'datetime']; }
    public function worksheet(): BelongsTo { return $this->belongsTo(AcademyWorksheet::class, 'academy_worksheet_id'); }
}
