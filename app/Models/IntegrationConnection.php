<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationConnection extends Model
{
    protected $guarded = [];
    protected $casts = ['configuration' => 'array', 'last_checked_at' => 'datetime'];
}
