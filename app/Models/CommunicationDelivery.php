<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunicationDelivery extends Model
{
    protected $fillable = [
        'institution_id', 'direction', 'channel', 'provider', 'event_key',
        'recipient_user_id', 'recipient_name', 'recipient_address', 'subject',
        'content', 'status', 'idempotency_key', 'provider_message_id', 'attempts',
        'scheduled_at', 'queued_at', 'sent_at', 'delivered_at', 'failed_at',
        'last_error', 'metadata', 'created_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'encrypted',
            'metadata' => 'array',
            'scheduled_at' => 'datetime',
            'queued_at' => 'datetime',
            'sent_at' => 'datetime',
            'delivered_at' => 'datetime',
            'failed_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function isRetryable(): bool
    {
        return $this->direction === 'outbound' && in_array($this->status, ['failed', 'queued'], true);
    }
}
