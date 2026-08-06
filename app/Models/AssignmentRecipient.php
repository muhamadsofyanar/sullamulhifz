<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssignmentRecipient extends Model
{
    protected $fillable = ['assignment_id','student_id','recipient_source','status','first_viewed_at','completed_at'];
    protected function casts(): array { return ['first_viewed_at'=>'datetime','completed_at'=>'datetime']; }
    public function assignment(): BelongsTo { return $this->belongsTo(Assignment::class); }
    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function submissions(): HasMany { return $this->hasMany(AssignmentSubmission::class); }
}
