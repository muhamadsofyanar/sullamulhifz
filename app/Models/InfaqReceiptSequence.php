<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfaqReceiptSequence extends Model
{
    protected $fillable = ['institution_id', 'year', 'last_number'];
    protected function casts(): array { return ['year' => 'integer', 'last_number' => 'integer']; }
}
