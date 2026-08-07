<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPortfolio extends Model
{
    use SoftDeletes;
    protected $guarded = [];
    protected $casts = ['occurred_on' => 'date', 'metadata' => 'array'];

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }
    public function media(): BelongsTo { return $this->belongsTo(MediaAsset::class, 'media_asset_id'); }
}
