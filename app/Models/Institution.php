<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'slug', 'legal_name', 'phone', 'email', 'address', 'timezone', 'status', 'settings'];

    protected function casts(): array
    {
        return ['settings' => 'array'];
    }

    public function users(): HasMany { return $this->hasMany(User::class); }
    public function students(): HasMany { return $this->hasMany(Student::class); }
    public function teachers(): HasMany { return $this->hasMany(Teacher::class); }
}
