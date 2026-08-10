<?php

namespace App\Http\Controllers\Teacher;

/** @phase 6.0 Memorization focus and periodic assessment */

use App\Http\Controllers\Controller;
use App\Models\ClassEnrollment;
use App\Models\GroupMembership;
use App\Models\Student;
use App\Models\StudentMemorizationAssessment;
use App\Models\StudentMemorizationFocus;
use App\Models\TeacherAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MemorizationFocusController extends Controller
{
    public function update(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudent($request, $student);
        $data = $request->validate([
            'focus_key' => ['required', Rule::in($this->focusKeys())],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);

        DB::transaction(function () use ($request, $student, $teacher, $data): void {
            Student::query()
                ->where('institution_id', $request->user()->institution_id)
                ->lockForUpdate()
                ->findOrFail($student->id);
            StudentMemorizationFocus::query()
                ->where('institution_id', $request->user()->institution_id)
                ->where('student_id', $student->id)
                ->active()
                ->lockForUpdate()
                ->get()
                ->each->update(['status' => 'closed', 'ended_at' => now()]);

            StudentMemorizationFocus::create([
                'institution_id' => $request->user()->institution_id,
                'student_id' => $student->id,
                'set_by_teacher_id' => $teacher->id,
                'focus_key' => $data['focus_key'],
                'status' => 'active',
                'notes' => $data['notes'] ?? null,
                'started_at' => now(),
            ]);
        }, 3);

        return back()->with('success', 'Fokus pembinaan aktif berhasil diperbarui. Fokus ini hanya pengingat, bukan peringkat.');
    }

    public function assess(Request $request, Student $student): RedirectResponse
    {
        $this->authorizeStudent($request, $student);
        $levels = ['strong', 'developing', 'needs_support'];
        $data = $request->validate([
            'assessment_type' => ['required', Rule::in(['initial', 'monthly', 'completion', 'tasmi', 'exam', 'stagnation'])],
            'assessed_on' => ['required', 'date', 'before_or_equal:today'],
            'accuracy_status' => ['required', Rule::in($levels)],
            'fluency_status' => ['required', Rule::in($levels)],
            'independence_status' => ['required', Rule::in($levels)],
            'makhraj_tajwid_status' => ['required', Rule::in($levels)],
            'retention_status' => ['required', Rule::in($levels)],
            'recommended_focus' => ['nullable', Rule::in($this->focusKeys())],
            'summary' => ['nullable', 'string', 'max:3000'],
        ]);

        StudentMemorizationAssessment::create([
            ...$data,
            'institution_id' => $request->user()->institution_id,
            'student_id' => $student->id,
            'teacher_id' => $request->user()->teacher?->id,
        ]);

        return back()->with('success', 'Asesmen berkala tersimpan. Fokus aktif tidak berubah sampai ustadz menetapkannya.');
    }

    /** @return array<int, string> */
    private function focusKeys(): array
    {
        return ['accuracy', 'fluency', 'independence', 'makhraj_tajwid', 'retention'];
    }

    private function authorizeStudent(Request $request, Student $student): void
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher && (int) $student->institution_id === (int) $request->user()->institution_id, 403);
        $assignments = TeacherAssignment::query()
            ->where('institution_id', $request->user()->institution_id)
            ->where('teacher_id', $teacher->id)
            ->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('valid_from')->orWhereDate('valid_from', '<=', today()))
            ->where(fn ($q) => $q->whereNull('valid_until')->orWhereDate('valid_until', '>=', today()))
            ->get();
        $classStudentIds = ClassEnrollment::query()
            ->whereIn('class_id', $assignments->pluck('class_id')->filter())
            ->where('status', 'active')->pluck('student_id');
        $groupStudentIds = GroupMembership::query()
            ->whereIn('learning_group_id', $assignments->pluck('learning_group_id')->filter())
            ->where('status', 'active')->pluck('student_id');

        abort_unless($classStudentIds->merge($groupStudentIds)->contains($student->id), 403, 'Santri tidak termasuk dalam penugasan guru ini.');
    }
}
