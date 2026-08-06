<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiaisonMessage extends Model
{
    protected $fillable = ['liaison_thread_id','sender_user_id','message','message_type'];
    public function thread(): BelongsTo { return $this->belongsTo(LiaisonThread::class, 'liaison_thread_id'); }
    public function sender(): BelongsTo { return $this->belongsTo(User::class, 'sender_user_id'); }
}
