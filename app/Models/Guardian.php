<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guardian extends Model
{
    use SoftDeletes;
    protected $fillable = ['institution_id','user_id','full_name','phone','email','address','occupation','status'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_guardians')
            ->withPivot([
                'relationship', 'is_primary_contact', 'can_receive_notifications',
                'can_submit_assignments', 'can_view_learning_records', 'started_at', 'ended_at',
            ])
            ->where(function ($query): void {
                $query->whereNull('student_guardians.started_at')
                    ->orWhereDate('student_guardians.started_at', '<=', today());
            })
            ->where(function ($query): void {
                $query->whereNull('student_guardians.ended_at')
                    ->orWhereDate('student_guardians.ended_at', '>=', today());
            })
            ->withTimestamps();
    }
}
