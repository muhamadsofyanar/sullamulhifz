<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCardItem extends Model
{
    protected $fillable = ['report_card_id','category','label','score','description','sort_order'];
    public function reportCard(): BelongsTo { return $this->belongsTo(ReportCard::class); }
}
