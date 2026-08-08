<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AcademyCertificate extends Model {
    protected $guarded = [];
    protected function casts(): array { return ['issued_at' => 'datetime']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function program(): BelongsTo { return $this->belongsTo(AcademyProgram::class, 'academy_program_id'); }
    public function institution(): BelongsTo { return $this->belongsTo(Institution::class); }
}
