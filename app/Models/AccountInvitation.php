<?php

namespace App\Models;

use App\Models\Concerns\BelongsToInstitution;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountInvitation extends Model
{
    use BelongsToInstitution;

    protected $fillable = [
        'institution_id', 'user_id', 'token_hash', 'delivery_channel', 'delivery_address',
        'expires_at', 'accepted_at', 'created_by_user_id',
    ];

    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'accepted_at' => 'datetime'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by_user_id'); }

    public function isValid(): bool
    {
        return $this->accepted_at === null && $this->expires_at->isFuture();
    }
}
