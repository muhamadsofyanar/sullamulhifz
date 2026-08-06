<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\LearningGroup;
use App\Models\MarhalahType;
use App\Models\Meeting;
use App\Models\MemorizationTarget;
use App\Models\MemorizationRecord;
use App\Models\MurajaahRecord;
use App\Models\QuranSurah;
use App\Models\SchoolClass;
use App\Models\TahsinRecord;
use App\Models\TeacherAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MeetingController extends Controller
{
    public function create(Request $request): View
    {
        $targetType=$request->string('target_type')->toString();
        $targetId=$request->integer('target_id');
        [$target,$assignment]=$this->resolveTarget($request,$targetType,$targetId);
        return view('teacher.meetings.create',compact('target','targetType','assignment'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data=$request->validate([
            'target_type'=>['required',Rule::in(['class','group'])],
            'target_id'=>['required','integer'],
            'meeting_date'=>['required','date'],
            'started_at'=>['nullable','date_format:H:i'],
            'topic'=>['nullable','string','max:190'],
        ]);
        [$target,$assignment]=$this->resolveTarget($request,$data['target_type'],$data['target_id']);
        $meeting=Meeting::create([
            'institution_id'=>$request->user()->institution_id,
            'class_id'=>$data['target_type']==='class'?$target->id:null,
            'learning_group_id'=>$data['target_type']==='group'?$target->id:null,
            'program_id'=>$assignment->program_id,
            'teacher_id'=>$request->user()->teacher->id,
            'meeting_date'=>$data['meeting_date'],
            'started_at'=>$data['started_at']?now()->setTimeFromTimeString($data['started_at']):now(),
            'topic'=>$data['topic'],
            'status'=>'ongoing',
        ]);
        return redirect()->route('teacher.meetings.show',$meeting)->with('success','Pertemuan dimulai. Silakan isi absensi dan catatan penting.');
    }

    public function show(Request $request, Meeting $meeting): View
    {
        $this->authorizeMeeting($request,$meeting);
        $students=$this->students($meeting);
        $meeting->load(['attendanceRecords','tahsinRecords','memorizationRecords','murajaahRecords','schoolClass','learningGroup','teacher']);
        return view('teacher.meetings.show',[
            'meeting'=>$meeting,
            'students'=>$students,
            'surahs'=>QuranSurah::orderBy('id')->get(),
            'marhalah'=>MarhalahType::where('status','active')->orderBy('sequence')->get(),
            'targets'=>MemorizationTarget::with(['student','rubu','surah','marhalah'])->whereIn('student_id',$students->pluck('id'))->whereIn('status',['active','in_progress','strengthening','paused'])->latest()->get(),
        ]);
    }

    public function attendance(Request $request, Meeting $meeting): View
    {
        $this->authorizeMeeting($request,$meeting);
        $students=$this->students($meeting);
        $existing=$meeting->attendanceRecords()->get()->keyBy('student_id');
        return view('teacher.meetings.attendance',compact('meeting','students','existing'));
    }

    public function storeAttendance(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorizeMeeting($request,$meeting);
        $allowedStudentIds=$this->students($meeting)->pluck('id');
        $data=$request->validate(['attendance'=>['required','array'],'attendance.*.status'=>['required',Rule::in(['present','late','permission','sick','absent'])],'attendance.*.notes'=>['nullable','string','max:500']]);
        DB::transaction(function() use($data,$allowedStudentIds,$meeting,$request):void{
            foreach($data['attendance'] as $studentId=>$record){
                if(!$allowedStudentIds->contains((int)$studentId)) continue;
                AttendanceRecord::updateOrCreate(['meeting_id'=>$meeting->id,'student_id'=>$studentId],['status'=>$record['status'],'notes'=>$record['notes']??null,'recorded_by'=>$request->user()->id]);
            }
        });
        return redirect()->route('teacher.meetings.show',$meeting)->with('success','Absensi berhasil disimpan.');
    }

    public function storeTahsin(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorizeMeeting($request,$meeting);
        $data=$request->validate([
            'student_id'=>['required','exists:students,id'],
            'material_text'=>['required','string','max:500'],
            'surah_id'=>['nullable','exists:quran_surahs,id'],
            'start_verse'=>['nullable','integer','min:1'],
            'end_verse'=>['nullable','integer','gte:start_verse'],
            'overall_status'=>['required',Rule::in(['good','practice_needed','guidance_needed','special_correction'])],
            'focus_categories'=>['nullable','array'],
            'focus_categories.*'=>['string','max:50'],
            'teacher_notes'=>['nullable','string'],
            'follow_up'=>['nullable','string','max:190'],
        ]);
        $this->authorizeStudent($meeting,(int)$data['student_id']);
        TahsinRecord::create([...$data,'institution_id'=>$meeting->institution_id,'meeting_id'=>$meeting->id,'teacher_id'=>$request->user()->teacher->id]);
        return back()->with('success','Catatan tahsīn berhasil disimpan.');
    }

    public function storeMemorization(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorizeMeeting($request,$meeting);
        $data=$request->validate([
            'student_id'=>['required','exists:students,id'],
            'marhalah_type_id'=>['nullable','exists:marhalah_types,id'],
            'record_type'=>['required',Rule::in(['new_memorization','initial_repetition','home_submission','class_submission','tasmi','exam'])],
            'surah_id'=>['required','exists:quran_surahs,id'],
            'start_verse'=>['required','integer','min:1'],
            'end_verse'=>['required','integer','gte:start_verse'],
            'result'=>['required',Rule::in(['fluent','fair','repeat_needed','postponed'])],
            'assistance_level'=>['required',Rule::in(['none','little','several','much'])],
            'follow_up'=>['nullable','string','max:190'],
            'teacher_notes'=>['nullable','string'],
        ]);
        $this->authorizeStudent($meeting,(int)$data['student_id']);
        $this->validateVerseRange((int)$data['surah_id'],(int)$data['end_verse']);
        MemorizationRecord::create([...$data,'institution_id'=>$meeting->institution_id,'meeting_id'=>$meeting->id,'teacher_id'=>$request->user()->teacher->id,'recorded_at'=>now()]);
        $target = MemorizationTarget::where('student_id',$data['student_id'])->where('surah_id',$data['surah_id'])->where('start_verse',$data['start_verse'])->where('end_verse',$data['end_verse'])->whereIn('status',['active','in_progress','strengthening','paused'])->latest()->first();
        if ($target) {
            $status = match ($data['result']) {
                'fluent' => 'completed',
                'fair' => 'in_progress',
                'repeat_needed' => 'strengthening',
                default => 'paused',
            };
            $target->update(['status'=>$status,'completed_at'=>$status==='completed'?now():null]);
        }
        return back()->with('success','Setoran hafalan berhasil disimpan dan target terkait diperbarui.');
    }

    public function storeMurajaah(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorizeMeeting($request,$meeting);
        $data=$request->validate([
            'student_id'=>['required','exists:students,id'],
            'murajaah_type'=>['required',Rule::in(['scheduled','random_recall','continuation','tasmi','home'])],
            'surah_id'=>['required','exists:quran_surahs,id'],
            'start_verse'=>['required','integer','min:1'],
            'end_verse'=>['required','integer','gte:start_verse'],
            'result'=>['required',Rule::in(['maintained','strengthening_needed','reactivation_needed'])],
            'assistance_level'=>['required',Rule::in(['none','little','several','much'])],
            'next_review_date'=>['nullable','date','after_or_equal:today'],
            'teacher_notes'=>['nullable','string'],
        ]);
        $this->authorizeStudent($meeting,(int)$data['student_id']);
        $this->validateVerseRange((int)$data['surah_id'],(int)$data['end_verse']);
        MurajaahRecord::create([...$data,'institution_id'=>$meeting->institution_id,'meeting_id'=>$meeting->id,'teacher_id'=>$request->user()->teacher->id,'recorded_at'=>now()]);
        $target = MemorizationTarget::where('student_id',$data['student_id'])->where('surah_id',$data['surah_id'])->where('start_verse',$data['start_verse'])->where('end_verse',$data['end_verse'])->whereIn('status',['active','in_progress','strengthening','paused'])->latest()->first();
        if ($target && in_array($target->target_type,['murajaah','initial_repetition'],true)) {
            $status = match ($data['result']) {
                'maintained' => 'completed',
                'strengthening_needed' => 'strengthening',
                default => 'in_progress',
            };
            $target->update(['status'=>$status,'completed_at'=>$status==='completed'?now():null]);
        }
        return back()->with('success','Catatan murāja‘ah berhasil disimpan dan target terkait diperbarui.');
    }

    public function finish(Request $request, Meeting $meeting): RedirectResponse
    {
        $this->authorizeMeeting($request,$meeting);
        $data=$request->validate(['general_notes'=>['nullable','string']]);
        $meeting->update(['general_notes'=>$data['general_notes']??null,'ended_at'=>now(),'status'=>'completed']);
        return redirect()->route('teacher.classrooms.index')->with('success','Pertemuan telah diselesaikan.');
    }

    private function resolveTarget(Request $request,string $type,int $id): array
    {
        if($type==='class'){
            $target=SchoolClass::findOrFail($id);
            $assignment=ClassroomController::classAssignment($request,$target);
            return [$target,$assignment];
        }
        if($type==='group'){
            $target=LearningGroup::findOrFail($id);
            $assignment=ClassroomController::groupAssignment($request,$target);
            return [$target,$assignment];
        }
        abort(422,'Jenis target tidak sesuai.');
    }

    private function students(Meeting $meeting)
    {
        if($meeting->class_id){
            return $meeting->schoolClass->activeEnrollments()->with('student')->get()->pluck('student')->sortBy('full_name')->values();
        }
        return $meeting->learningGroup->activeMemberships()->with('student')->get()->pluck('student')->sortBy('full_name')->values();
    }

    private function authorizeMeeting(Request $request,Meeting $meeting): void
    {
        abort_unless($request->user()->teacher && $meeting->teacher_id===$request->user()->teacher->id,403);
    }

    private function authorizeStudent(Meeting $meeting,int $studentId): void
    {
        abort_unless($this->students($meeting)->pluck('id')->contains($studentId),403,'Santri tidak termasuk dalam pertemuan ini.');
    }

    private function validateVerseRange(int $surahId,int $endVerse): void
    {
        $surah=QuranSurah::findOrFail($surahId);
        abort_if($endVerse>$surah->verse_count,422,'Rentang ayat melebihi jumlah ayat surah.');
    }
}
