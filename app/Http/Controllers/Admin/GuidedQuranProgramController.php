<?php

namespace App\Http\Controllers\Admin;

/** @phase 4.5 workspace-scoped teacher relation hotfix */

use App\Http\Controllers\Controller;
use App\Models\AcademyProgram;
use App\Models\GuidedQuranEnrollment;
use App\Models\GuidedQuranProgram;
use App\Models\GuidedQuranProgramReviewer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GuidedQuranProgramController extends Controller
{
    public function index(Request $request): View
    {
        $institutionId = (int) $request->user()->institution_id;
        $programs = GuidedQuranProgram::query()
            ->with(['academyProgram', 'reviewers.reviewer', 'enrollments.student'])
            ->where('provider_institution_id', $institutionId)
            ->latest()
            ->get();
        $teachers = User::query()
            ->where('institution_id', $institutionId)
            ->whereHas('roles', fn ($q) => $q->where('roles.name', 'teacher')->where('user_roles.status', 'active'))
            ->orderBy('name')
            ->get();

        return view('admin.guided-learning.index', [
            'programs' => $programs,
            'teachers' => $teachers,
            'students' => Student::query()->where('institution_id', $institutionId)->where('status', 'active')->orderBy('full_name')->get(),
            'academyPrograms' => AcademyProgram::query()->where('institution_id', $institutionId)->where('status', 'published')->whereIn('audience', ['all', 'personal'])->orderBy('title')->get(),
        ]);
    }

    public function storeProgram(Request $request): RedirectResponse
    {
        $institutionId = (int) $request->user()->institution_id;
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'program_type' => ['required', Rule::in(['tahfizh', 'reading', 'tahsin', 'murajaah'])],
            'delivery_mode' => ['required', Rule::in(['online', 'offline', 'hybrid'])],
            'target_juz' => ['nullable', 'integer', 'between:1,30'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string', 'max:10000'],
            'submission_guidance' => ['nullable', 'string', 'max:5000'],
            'academy_program_id' => ['nullable', Rule::exists('academy_programs', 'id')->where('institution_id', $institutionId)],
            'accepts_audio' => ['nullable', 'boolean'],
            'accepts_text' => ['nullable', 'boolean'],
            'is_public' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
        ]);

        $baseSlug = Str::slug($data['title']) ?: 'program-quran';
        $slug = $baseSlug;
        $suffix = 2;
        while (GuidedQuranProgram::query()->where('provider_institution_id', $institutionId)->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix++;
        }

        GuidedQuranProgram::query()->create([
            ...$data,
            'provider_institution_id' => $institutionId,
            'created_by_user_id' => $request->user()->id,
            'slug' => $slug,
            'accepts_audio' => (bool) ($data['accepts_audio'] ?? false),
            'accepts_text' => (bool) ($data['accepts_text'] ?? false),
            'is_public' => (bool) ($data['is_public'] ?? false),
        ]);

        return back()->with('success', 'Program Al-Qur’an dibuat. Tambahkan reviewer sebelum menerima setoran online.');
    }

    public function assignReviewer(Request $request, GuidedQuranProgram $program): RedirectResponse
    {
        $this->authorizeProgram($request, $program);
        $data = $request->validate(['reviewer_user_id' => ['required', 'integer', 'exists:users,id']]);
        $reviewer = User::query()->findOrFail($data['reviewer_user_id']);
        abort_unless((int) $reviewer->institution_id === (int) $program->provider_institution_id && $reviewer->hasRole('teacher') && $reviewer->teacher, 422, 'Reviewer harus asatidz aktif dari lembaga penyelenggara.');

        GuidedQuranProgramReviewer::query()->updateOrCreate(
            ['guided_quran_program_id' => $program->id, 'reviewer_user_id' => $reviewer->id],
            ['reviewer_teacher_id' => $reviewer->teacher->id, 'added_by_user_id' => $request->user()->id, 'status' => 'active'],
        );

        return back()->with('success', 'Asatidz ditambahkan sebagai reviewer program.');
    }

    public function enrollStudent(Request $request, GuidedQuranProgram $program): RedirectResponse
    {
        $this->authorizeProgram($request, $program);
        $data = $request->validate(['student_id' => ['required', 'integer', 'exists:students,id']]);
        $student = Student::query()->where('institution_id', $request->user()->institution_id)->findOrFail($data['student_id']);

        GuidedQuranEnrollment::query()->firstOrCreate(
            ['guided_quran_program_id' => $program->id, 'student_id' => $student->id],
            [
                'learner_institution_id' => $student->institution_id,
                'learner_user_id' => null,
                'learner_mode' => $program->delivery_mode === 'online' ? 'institution_online' : 'institution_hybrid',
                'status' => 'active',
                'enrolled_at' => now(),
                'metadata' => ['joined_via' => 'provider_admin'],
            ],
        );

        return back()->with('success', 'Santri lembaga ditambahkan ke program. Setoran offline tetap dicatat melalui alur Tahfizh/pertemuan yang sudah ada.');
    }

    private function authorizeProgram(Request $request, GuidedQuranProgram $program): void
    {
        abort_unless((int) $program->provider_institution_id === (int) $request->user()->institution_id, 404);
    }
}
