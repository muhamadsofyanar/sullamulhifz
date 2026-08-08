<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassEnrollment;
use App\Models\GroupMembership;
use App\Models\LearningInsight;
use App\Models\LearningObservation;
use App\Models\LearningRecommendationReview;
use App\Models\Student;
use App\Models\TeacherAssignment;
use App\Services\PersonalLearningRecommendationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersonalLearningController extends Controller
{
    public function __construct(private readonly PersonalLearningRecommendationService $recommendations)
    {
    }

    public function index(Request $request): View
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403, 'Profil guru belum terhubung.');
        $institutionId = (int) $request->user()->institution_id;
        $students = $this->students((int) $teacher->id, $institutionId);
        $selected = null;

        if ($request->filled('student_id')) {
            $selected = $students->firstWhere('id', (int) $request->integer('student_id'));
            abort_unless($selected, 403, 'Santri tidak termasuk dalam penugasan guru ini.');
        }

        return view('teacher.personal-learning.index', [
            'students' => $students,
            'selected' => $selected,
            'observations' => $selected
                ? LearningObservation::query()->where('institution_id',$institutionId)->where('student_id',$selected->id)->latest('observed_at')->limit(12)->get()
                : collect(),
            'insights' => $selected
                ? LearningInsight::query()->with('recommendationReview')->where('institution_id',$institutionId)->where('student_id',$selected->id)
                    ->where('insight_type','personal_recommendation')->latest('generated_at')->limit(10)->get()
                : collect(),
        ]);
    }

    public function generate(Request $request, Student $student): RedirectResponse
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);
        $institutionId = (int) $request->user()->institution_id;
        $this->authorizeStudent($student, (int) $teacher->id, $institutionId);

        $this->recommendations->generate($student, $teacher, (int) $request->user()->id);

        return redirect()->route('teacher.personal-learning.index',['student_id'=>$student->id])
            ->with('success','Draf rekomendasi dibuat dari evidence belajar. Keputusan belum berlaku sebelum ditinjau guru.');
    }

    public function review(Request $request, LearningInsight $insight): RedirectResponse
    {
        $teacher = $request->user()->teacher;
        abort_unless($teacher, 403);
        $institutionId = (int) $request->user()->institution_id;
        abort_unless((int) $insight->institution_id === $institutionId && $insight->insight_type === 'personal_recommendation', 404);
        $student = Student::where('institution_id',$institutionId)->findOrFail($insight->student_id);
        $this->authorizeStudent($student, (int) $teacher->id, $institutionId);
        abort_if($insight->recommendationReview()->exists(), 422, 'Rekomendasi ini sudah direview.');

        $data = $request->validate([
            'decision' => ['required', Rule::in(['accepted','modified','rejected'])],
            'final_recommendation' => ['nullable','string','max:10000'],
            'review_note' => ['nullable','string','max:3000'],
        ]);
        if ($data['decision'] === 'modified') {
            abort_if(trim((string) ($data['final_recommendation'] ?? '')) === '', 422, 'Tuliskan rekomendasi hasil perubahan guru.');
        }

        $final = match ($data['decision']) {
            'accepted' => $insight->summary,
            'modified' => trim((string) $data['final_recommendation']),
            default => null,
        };

        DB::transaction(function () use ($insight, $student, $teacher, $institutionId, $data, $final): void {
            LearningRecommendationReview::create([
                'institution_id' => $institutionId,
                'learning_insight_id' => $insight->id,
                'student_id' => $student->id,
                'teacher_id' => $teacher->id,
                'decision' => $data['decision'],
                'original_recommendation' => $insight->summary,
                'final_recommendation' => $final,
                'review_note' => $data['review_note'] ?? null,
                'reviewed_at' => now(),
            ]);
            $insight->update(['status' => $data['decision']]);
        });

        return redirect()->route('teacher.personal-learning.index',['student_id'=>$student->id])
            ->with('success','Keputusan guru tersimpan. Rekomendasi sistem tidak pernah menggantikan keputusan guru.');
    }

    private function authorizeStudent(Student $student, int $teacherId, int $institutionId): void
    {
        abort_unless((int) $student->institution_id === $institutionId, 404);
        abort_unless($this->students($teacherId, $institutionId)->contains('id',$student->id), 403, 'Santri tidak termasuk dalam penugasan guru ini.');
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
