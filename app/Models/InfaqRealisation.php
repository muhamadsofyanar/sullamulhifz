<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InfaqRealisation extends Model
{
    protected $fillable = ['public_id', 'institution_id', 'category', 'program_name', 'purpose', 'amount', 'beneficiary_count', 'impact_summary', 'realised_on', 'status', 'created_by_user_id', 'reviewed_by_user_id', 'submitted_at', 'reviewed_at', 'review_note'];
    protected function casts(): array { return ['amount' => 'decimal:2', 'beneficiary_count' => 'integer', 'realised_on' => 'date', 'submitted_at' => 'datetime', 'reviewed_at' => 'datetime']; }
    public function evidences(): HasMany { return $this->hasMany(InfaqEvidence::class, 'realisation_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by_user_id'); }
}
