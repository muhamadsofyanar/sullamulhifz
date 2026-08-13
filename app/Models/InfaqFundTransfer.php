<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfaqFundTransfer extends Model
{
    protected $fillable = ['public_id', 'institution_id', 'from_category', 'to_category', 'amount', 'reason', 'status', 'created_by_user_id', 'approved_by_user_id', 'approved_at', 'review_note'];
    protected function casts(): array { return ['amount' => 'decimal:2', 'approved_at' => 'datetime']; }
}
