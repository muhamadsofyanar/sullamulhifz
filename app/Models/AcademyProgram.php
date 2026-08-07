<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademyProgram extends Model
{
    protected $fillable = ['institution_id','title','slug','audience','category','learning_track','summary','description','cover_url','status','is_featured','sort_order','metadata'];
    protected function casts(): array { return ['is_featured' => 'boolean', 'metadata' => 'array']; }
    public function modules(): HasMany { return $this->hasMany(AcademyModule::class)->orderBy('sort_order')->orderBy('id'); }
}
