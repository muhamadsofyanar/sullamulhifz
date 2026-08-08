<?php

namespace App\Services;

use App\Models\QuranProgramEnrollment;
use App\Models\QuranProgramProgress;
use App\Models\QuranProgramTemplate;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class QuranProgramService
{
    public function startForUser(int $institutionId, User $user, QuranProgramTemplate $template, string $purpose, string $scheduleMode, string $startDate, ?string $notes = null): QuranProgramEnrollment
    {
        return $this->start($institutionId, $template, $purpose, $scheduleMode, $startDate, $notes, null, $user, null);
    }

    public function startForStudent(Student $student, Teacher $teacher, QuranProgramTemplate $template, string $purpose, string $scheduleMode, string $startDate, ?string $notes = null): QuranProgramEnrollment
    {
        return $this->start((int)$student->institution_id, $template, $purpose, $scheduleMode, $startDate, $notes, $student, null, $teacher);
    }

    private function start(int $institutionId, QuranProgramTemplate $template, string $purpose, string $scheduleMode, string $startDate, ?string $notes, ?Student $student, ?User $user, ?Teacher $teacher): QuranProgramEnrollment
    {
        if (! in_array($purpose, ['tilawah','murajaah','both'], true)) {
            throw ValidationException::withMessages(['purpose'=>'Tujuan program Qur’an tidak dikenal.']);
        }
        if (! in_array($scheduleMode, ['daily','flexible'], true)) {
            throw ValidationException::withMessages(['schedule_mode'=>'Mode jadwal tidak dikenal.']);
        }

        return DB::transaction(function () use ($institutionId,$template,$purpose,$scheduleMode,$startDate,$notes,$student,$user,$teacher): QuranProgramEnrollment {
            $enrollment = QuranProgramEnrollment::query()->create([
                'institution_id'=>$institutionId,
                'quran_program_template_id'=>$template->id,
                'student_id'=>$student?->id,
                'user_id'=>$user?->id,
                'assigned_by_teacher_id'=>$teacher?->id,
                'purpose'=>$purpose,
                'schedule_mode'=>$scheduleMode,
                'start_date'=>$startDate,
                'target_end_date'=>$scheduleMode === 'daily' && $template->duration_days
                    ? Carbon::parse($startDate)->addDays(max(0,(int)$template->duration_days - 1))->toDateString()
                    : null,
                'status'=>'active',
                'current_step'=>1,
                'notes'=>$notes,
            ]);

            $template->loadMissing('steps');
            foreach ($template->steps as $step) {
                QuranProgramProgress::query()->create([
                    'quran_program_enrollment_id'=>$enrollment->id,
                    'quran_program_step_id'=>$step->id,
                    'status'=>'pending',
                ]);
            }

            return $enrollment->fresh(['template.steps','progress.step']);
        });
    }

    public function markStep(QuranProgramEnrollment $enrollment, int $stepId, string $status, ?string $notes = null): QuranProgramEnrollment
    {
        if (! in_array($status, ['pending','in_progress','completed'], true)) {
            throw ValidationException::withMessages(['status'=>'Status langkah program tidak dikenal.']);
        }

        DB::transaction(function () use ($enrollment,$stepId,$status,$notes): void {
            $progress = QuranProgramProgress::query()
                ->where('quran_program_enrollment_id',$enrollment->id)
                ->where('quran_program_step_id',$stepId)
                ->firstOrFail();

            $progress->update([
                'status'=>$status,
                'started_at'=>in_array($status,['in_progress','completed'],true) ? ($progress->started_at ?: now()) : null,
                'completed_at'=>$status === 'completed' ? now() : null,
                'notes'=>$notes ?? $progress->notes,
            ]);

            $steps = QuranProgramProgress::query()->with('step')
                ->where('quran_program_enrollment_id',$enrollment->id)
                ->get()->sortBy(fn($item)=>$item->step?->sequence ?? 9999);
            $next = $steps->first(fn($item)=>$item->status !== 'completed');
            $complete = $next === null && $steps->isNotEmpty();

            $enrollment->update([
                'current_step'=>$complete ? $steps->count() : (int)($next?->step?->sequence ?? 1),
                'status'=>$complete ? 'completed' : 'active',
            ]);
        });

        return $enrollment->fresh(['template.steps','progress.step']);
    }

    /** @return array<string,int> */
    public function progressSummary(QuranProgramEnrollment $enrollment): array
    {
        $total = $enrollment->progress()->count();
        $completed = $enrollment->progress()->where('status','completed')->count();
        return [
            'total'=>$total,
            'completed'=>$completed,
            'percent'=>$total > 0 ? (int)round(($completed/$total)*100) : 0,
        ];
    }
}
