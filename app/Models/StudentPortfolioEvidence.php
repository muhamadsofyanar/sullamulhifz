<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPortfolioEvidence extends Model
{
    protected $guarded = [];
    protected function casts(): array { return ['metadata' => 'array', 'occurred_on' => 'date']; }
    public function portfolio(): BelongsTo { return $this->belongsTo(StudentPortfolio::class, 'student_portfolio_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function mediaAsset(): BelongsTo { return $this->belongsTo(MediaAsset::class); }
}
