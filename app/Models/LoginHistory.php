<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginHistory extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id','institution_id','login_identifier','was_successful','ip_address','user_agent','logged_in_at'];

    protected function casts(): array
    {
        return ['was_successful' => 'boolean', 'logged_in_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
