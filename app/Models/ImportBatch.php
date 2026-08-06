<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    protected $fillable = ['institution_id','uploaded_by_user_id','type','original_name','status','total_rows','success_rows','failed_rows','summary'];
    protected function casts(): array { return ['summary'=>'array']; }
    public function rows(): HasMany { return $this->hasMany(ImportRow::class); }
}
