<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\GroupMembership;
use App\Models\LearningGroup;
use App\Models\Level;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicController extends Controller
{
    public function index(Request $request): View
    {
        $institutionId = $request->user()->institution_id;
        return view('admin.academic.index', [
            'year'=>AcademicYear::where('institution_id',$institutionId)->where('is_active',true)->first(),
            'years'=>AcademicYear::where('institution_id',$institutionId)->latest('start_date')->get(),
            'levels'=>Level::where('institution_id',$institutionId)->orderBy('sequence')->get(),
            'branches'=>Branch::where('institution_id',$institutionId)->where('status','active')->orderByDesc('is_main')->orderBy('name')->get(),
            'classes'=>SchoolClass::with(['level','branch','activeEnrollments'])->where('institution_id',$institutionId)->orderBy('name')->get(),
            'groups'=>LearningGroup::with(['program','branch','activeMemberships'])->where('institution_id',$institutionId)->orderBy('name')->get(),
            'programs'=>Program::where('institution_id',$institutionId)->where('status','active')->orderBy('name')->get(),
            'teachers'=>Teacher::where('institution_id',$institutionId)->where('status','active')->orderBy('full_name')->get(),
            'assignments'=>TeacherAssignment::with(['teacher','schoolClass','learningGroup','program'])->where('institution_id',$institutionId)->where('status','active')->get(),
            'schedules'=>Schedule::with(['branch','schoolClass','learningGroup','program','teacherAssignment.teacher'])->where('institution_id',$institutionId)->where('status','active')->orderBy('day_of_week')->orderBy('start_time')->get(),
        ]);
    }


    public function storeYear(Request $request): RedirectResponse
    {
        $institutionId = $request->user()->institution_id;
        $data = $request->validate([
            'name' => ['required','string','max:30', Rule::unique('academic_years')->where('institution_id',$institutionId)],
            'start_date' => ['required','date'],
            'end_date' => ['required','date','after:start_date'],
            'is_active' => ['nullable','boolean'],
        ]);
        if ((bool)($data['is_active'] ?? false)) {
            AcademicYear::where('institution_id',$institutionId)->update(['is_active'=>false,'status'=>'closed']);
        }
        $year = AcademicYear::create([...$data,'institution_id'=>$institutionId,'is_active'=>(bool)($data['is_active']??false),'status'=>(bool)($data['is_active']??false)?'active':'draft']);
        AcademicPeriod::create([
            'academic_year_id' => $year->id,
            'name' => 'Periode Utama',
            'start_date' => $year->start_date,
            'end_date' => $year->end_date,
            'status' => $year->is_active ? 'active' : 'draft',
        ]);
        return back()->with('success','Tahun ajaran dan periode utama berhasil ditambahkan.');
    }

    public function storeLevel(Request $request): RedirectResponse
    {
        $institutionId = $request->user()->institution_id;
        $data = $request->validate([
            'name'=>['required','string','max:100'],
            'code'=>['required','string','max:50',Rule::unique('levels')->where('institution_id',$institutionId)],
            'sequence'=>['nullable','integer','min:0','max:999'],
            'description'=>['nullable','string'],
        ]);
        Level::create([...$data,'institution_id'=>$institutionId,'sequence'=>$data['sequence']??0,'status'=>'active']);
        return back()->with('success','Jenjang berhasil ditambahkan.');
    }

    public function storeProgram(Request $request): RedirectResponse
    {
        $institutionId = $request->user()->institution_id;
        $data = $request->validate([
            'name'=>['required','string','max:100'],
            'code'=>['required','string','max:50',Rule::unique('programs')->where('institution_id',$institutionId)],
            'category'=>['required',Rule::in(['quran','character','talent','parenting','community'])],
            'description'=>['nullable','string'],
        ]);
        Program::create([...$data,'institution_id'=>$institutionId,'status'=>'active']);
        return back()->with('success','Program berhasil ditambahkan.');
    }

    public function storeClass(Request $request): RedirectResponse
    {
        $institutionId = $request->user()->institution_id;
        $data = $request->validate([
            'academic_year_id'=>['required','exists:academic_years,id'],
            'branch_id'=>['nullable','exists:branches,id'],
            'level_id'=>['required','exists:levels,id'],
            'name'=>['required','string','max:190'],
            'code'=>['required','string','max:50',Rule::unique('classes')->where('academic_year_id',$request->integer('academic_year_id'))],
            'capacity'=>['nullable','integer','min:1','max:500'],
        ]);
        abort_unless(AcademicYear::where('institution_id',$institutionId)->whereKey($data['academic_year_id'])->exists(),403);
        abort_unless(Level::where('institution_id',$institutionId)->whereKey($data['level_id'])->exists(),403);
        $data['branch_id'] = $this->resolveBranchId($institutionId, $data['branch_id'] ?? null);
        SchoolClass::create([...$data,'institution_id'=>$institutionId,'status'=>'active']);
        return back()->with('success','Kelas berhasil ditambahkan.');
    }

    public function storeGroup(Request $request): RedirectResponse
    {
        $institutionId = $request->user()->institution_id;
        $data = $request->validate([
            'academic_year_id'=>['required','exists:academic_years,id'],
            'branch_id'=>['nullable','exists:branches,id'],
            'program_id'=>['required','exists:programs,id'],
            'name'=>['required','string','max:190'],
            'code'=>['required','string','max:50',Rule::unique('learning_groups')->where('academic_year_id',$request->integer('academic_year_id'))],
            'capacity'=>['nullable','integer','min:1','max:500'],
        ]);
        abort_unless(AcademicYear::where('institution_id',$institutionId)->whereKey($data['academic_year_id'])->exists(),403);
        abort_unless(Program::where('institution_id',$institutionId)->whereKey($data['program_id'])->exists(),403);
        $data['branch_id'] = $this->resolveBranchId($institutionId, $data['branch_id'] ?? null);
        LearningGroup::create([...$data,'institution_id'=>$institutionId,'status'=>'active']);
        return back()->with('success','Kelompok belajar berhasil ditambahkan.');
    }

    public function group(Request $request, LearningGroup $group): View
    {
        abort_unless($group->institution_id===$request->user()->institution_id,404);
        $group->load(['program','memberships.student.currentEnrollment.schoolClass']);
        $students = Student::with('currentEnrollment.schoolClass')->where('institution_id',$group->institution_id)->where('status','active')->orderBy('full_name')->get();
        return view('admin.academic.group', compact('group','students'));
    }

    public function addGroupMember(Request $request, LearningGroup $group): RedirectResponse
    {
        abort_unless($group->institution_id===$request->user()->institution_id,404);
        $data=$request->validate(['student_id'=>['required','exists:students,id']]);
        abort_unless(Student::where('institution_id',$group->institution_id)->whereKey($data['student_id'])->exists(),403);
        GroupMembership::updateOrCreate(['learning_group_id'=>$group->id,'student_id'=>$data['student_id']],['joined_at'=>now()->toDateString(),'ended_at'=>null,'status'=>'active']);
        return back()->with('success','Santri berhasil ditambahkan ke kelompok.');
    }

    public function storeTeacherAssignment(Request $request): RedirectResponse
    {
        $institutionId=$request->user()->institution_id;
        $data=$request->validate([
            'academic_year_id'=>['required','exists:academic_years,id'],
            'teacher_id'=>['required','exists:teachers,id'],
            'target_type'=>['required',Rule::in(['class','group'])],
            'class_id'=>['nullable','exists:classes,id'],
            'learning_group_id'=>['nullable','exists:learning_groups,id'],
            'program_id'=>['required','exists:programs,id'],
            'valid_from'=>['nullable','date'],
            'valid_until'=>['nullable','date','after_or_equal:valid_from'],
        ]);
        if ($data['target_type']==='class' && empty($data['class_id'])) return back()->withErrors(['class_id'=>'Pilih kelas.'])->withInput();
        if ($data['target_type']==='group' && empty($data['learning_group_id'])) return back()->withErrors(['learning_group_id'=>'Pilih kelompok.'])->withInput();
        abort_unless(AcademicYear::where('institution_id',$institutionId)->whereKey($data['academic_year_id'])->exists(),403);
        abort_unless(Teacher::where('institution_id',$institutionId)->whereKey($data['teacher_id'])->exists(),403);
        abort_unless(Program::where('institution_id',$institutionId)->whereKey($data['program_id'])->exists(),403);
        if ($data['target_type']==='class') abort_unless(SchoolClass::where('institution_id',$institutionId)->whereKey($data['class_id'])->exists(),403);
        if ($data['target_type']==='group') abort_unless(LearningGroup::where('institution_id',$institutionId)->whereKey($data['learning_group_id'])->exists(),403);
        $assignmentData=$data; unset($assignmentData['target_type']);
        TeacherAssignment::create([...$assignmentData,'institution_id'=>$institutionId,'class_id'=>$data['target_type']==='class'?$data['class_id']:null,'learning_group_id'=>$data['target_type']==='group'?$data['learning_group_id']:null,'assignment_role'=>'lead','status'=>'active']);
        return back()->with('success','Penugasan guru berhasil disimpan.');
    }

    public function storeSchedule(Request $request): RedirectResponse
    {
        $institutionId=$request->user()->institution_id;
        $data=$request->validate([
            'academic_year_id'=>['required','exists:academic_years,id'],
            'teacher_assignment_id'=>['required','exists:teacher_assignments,id'],
            'day_of_week'=>['required','integer','between:1,7'],
            'start_time'=>['required','date_format:H:i'],
            'end_time'=>['required','date_format:H:i','after:start_time'],
            'location'=>['nullable','string','max:190'],
        ]);
        $teacherAssignment = TeacherAssignment::where('institution_id',$institutionId)->findOrFail($data['teacher_assignment_id']);
        Schedule::create([
            ...$data,
            'institution_id'=>$institutionId,
            'branch_id'=>$teacherAssignment->schoolClass?->branch_id ?? $teacherAssignment->learningGroup?->branch_id ?? $this->resolveBranchId($institutionId),
            'class_id'=>$teacherAssignment->class_id,
            'learning_group_id'=>$teacherAssignment->learning_group_id,
            'program_id'=>$teacherAssignment->program_id,
            'status'=>'active',
        ]);
        return back()->with('success','Jadwal berhasil disimpan.');
    }

    private function resolveBranchId(int $institutionId, ?int $branchId = null): int
    {
        if ($branchId) {
            return Branch::where('institution_id', $institutionId)->where('status', 'active')->findOrFail($branchId)->id;
        }

        return Branch::where('institution_id', $institutionId)
            ->where('status', 'active')
            ->orderByDesc('is_main')
            ->value('id') ?? abort(422, 'Cabang utama belum tersedia.');
    }
}
