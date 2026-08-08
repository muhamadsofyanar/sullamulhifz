<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AcademyLesson;
use App\Models\ClassEnrollment;
use App\Models\FamilyLearningActivity;
use App\Models\GroupMembership;
use App\Models\Student;
use App\Models\TeacherAssignment;
use App\Models\TeacherCompetency;
use App\Models\TeacherCompetencyProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FamilyTeacherController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403, 'Profil guru belum terhubung.');
        $institutionId = (int) $request->user()->institution_id;
        $students = $this->students((int) $teacher->id, $institutionId);

        return view('teacher.family-teacher.index', [
            'students' => $students,
            'parentLessons' => AcademyLesson::query()->with('module.program')
                ->where('status','published')
                ->whereHas('module.program', fn ($q) => $q->where('institution_id',$institutionId)->where('status','published')->whereIn('audience',['guardian','all']))
                ->orderBy('title')->get(),
            'activities' => FamilyLearningActivity::query()->with(['student','lesson','completedBy'])
                ->where('institution_id',$institutionId)->where('created_by_user_id',$request->user()->id)
                ->latest()->limit(30)->get(),
            'competencies' => TeacherCompetency::query()->with(['lesson','progress' => fn ($q) => $q->where('teacher_id',$teacher->id)])
                ->where('institution_id',$institutionId)->where('status','active')->orderBy('sort_order')->orderBy('title')->get(),
        ]);
    }

    public function storeActivity(Request $request): RedirectResponse
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);
        $institutionId = (int) $request->user()->institution_id;
        $data = $request->validate([
            'student_id'=>['required','integer','exists:students,id'],
            'academy_lesson_id'=>['nullable','integer','exists:academy_lessons,id'],
            'title'=>['required','string','max:180'],
            'activity_type'=>['required',Rule::in(['practice','conversation','habit','reflection','project'])],
            'instructions'=>['required','string','max:5000'],
            'due_at'=>['nullable','date'],
        ]);

        abort_unless($this->students((int) $teacher->id, $institutionId)->contains('id',(int) $data['student_id']), 403);
        if (! empty($data['academy_lesson_id'])) {
            $lesson = AcademyLesson::with('module.program')->findOrFail($data['academy_lesson_id']);
            abort_unless(
                (int) $lesson->module?->program?->institution_id === $institutionId
                && $lesson->status === 'published'
                && $lesson->module?->status === 'published'
                && $lesson->module?->program?->status === 'published'
                && in_array($lesson->module?->program?->audience, ['guardian','all'], true),
                403
            );
        }

        FamilyLearningActivity::create([...$data,
            'institution_id'=>$institutionId,
            'created_by_user_id'=>$request->user()->id,
            'status'=>'assigned',
        ]);

        return back()->with('success','Aktivitas keluarga dikirim. Wali dapat menyelesaikan dan menulis refleksi tanpa skor.');
    }

    public function reviewActivity(Request $request, FamilyLearningActivity $activity): RedirectResponse
    {
        abort_unless((int) $activity->institution_id === (int) $request->user()->institution_id, 404);
        abort_unless((int) $activity->created_by_user_id === (int) $request->user()->id, 403);
        abort_unless($activity->status === 'completed', 422, 'Aktivitas belum diselesaikan wali.');
        $data = $request->validate(['teacher_follow_up'=>['nullable','string','max:3000']]);
        $activity->update(['teacher_follow_up'=>$data['teacher_follow_up'] ?? null,'status'=>'reviewed','reviewed_at'=>now()]);
        return back()->with('success','Tindak lanjut keluarga sudah direview.');
    }

    public function submitCompetency(Request $request, TeacherCompetency $competency): RedirectResponse
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);
        abort_unless((int) $competency->institution_id === (int) $request->user()->institution_id && $competency->status === 'active', 404);
        $data = $request->validate([
            'status'=>['required',Rule::in(['in_progress','reflection_submitted'])],
            'reflection'=>['nullable','string','max:10000'],
            'evidence_note'=>['nullable','string','max:10000'],
        ]);
        if ($data['status'] === 'reflection_submitted') {
            abort_if(trim((string) ($data['reflection'] ?? '')) === '', 422, 'Refleksi diperlukan sebelum dikirim untuk review.');
        }
        TeacherCompetencyProgress::updateOrCreate(
            ['teacher_id'=>$teacher->id,'teacher_competency_id'=>$competency->id],
            [...$data,'institution_id'=>$request->user()->institution_id,'submitted_at'=>$data['status']==='reflection_submitted' ? now() : null,'reviewed_by_user_id'=>null,'review_note'=>null,'reviewed_at'=>null]
        );
        return back()->with('success',$data['status']==='reflection_submitted' ? 'Refleksi kompetensi dikirim untuk review.' : 'Progres kompetensi disimpan.');
    }

    private function students(int $teacherId, int $institutionId): Collection
    {
        $assignments = TeacherAssignment::query()->where('institution_id',$institutionId)->where('teacher_id',$teacherId)->currentlyActive()->get();
        $ids = ClassEnrollment::query()->whereIn('class_id',$assignments->pluck('class_id')->filter())
            ->where('status','active')->where(fn ($q) => $q->whereNull('ended_at')->orWhereDate('ended_at','>=',today()))->pluck('student_id')
            ->merge(GroupMembership::query()->whereIn('learning_group_id',$assignments->pluck('learning_group_id')->filter())
                ->where('status','active')->where(fn ($q) => $q->whereNull('ended_at')->orWhereDate('ended_at','>=',today()))->pluck('student_id'))->unique();
        return Student::query()->where('institution_id',$institutionId)->whereIn('id',$ids)->where('status','active')->orderBy('full_name')->get();
    }
}
