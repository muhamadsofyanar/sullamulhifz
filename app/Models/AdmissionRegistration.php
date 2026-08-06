<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionRegistration extends Model
{
    protected $fillable = [
        'institution_id','student_name','student_age','guardian_name','guardian_phone','guardian_email',
        'desired_program','notes','status','handled_by_user_id','handled_at',
    ];

    protected function casts(): array
    {
        return ['handled_at' => 'datetime'];
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by_user_id');
    }
}
