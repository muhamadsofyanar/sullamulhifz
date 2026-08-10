<?php

namespace App\Http\Controllers\Admin;

/** @phase 6.0 PHP 8.4 report export syntax compatibility */

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\AssignmentRecipient;
use App\Models\Guardian;
use App\Models\MemorizationRecord;
use App\Models\MurajaahRecord;
use App\Models\Student;
use App\Models\TahsinRecord;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $institutionId = $request->user()->institution_id;
        return view('admin.reports.index', [
            'studentCount' => Student::where('institution_id',$institutionId)->where('status','active')->count(),
            'guardianCount' => Guardian::where('institution_id',$institutionId)->where('status','active')->count(),
            'attendanceCount' => AttendanceRecord::whereHas('meeting',fn($q)=>$q->where('institution_id',$institutionId))->count(),
            'tahsinCount' => TahsinRecord::where('institution_id',$institutionId)->count(),
            'memorizationCount' => MemorizationRecord::where('institution_id',$institutionId)->count(),
            'murajaahCount' => MurajaahRecord::where('institution_id',$institutionId)->count(),
        ]);
    }

    public function studentsCsv(Request $request): StreamedResponse
    {
        $students=Student::with(['currentEnrollment.schoolClass','guardians'])->where('institution_id',$request->user()->institution_id)->orderBy('full_name')->get();
        return $this->csv('data-santri', ['Kode','Nama','Panggilan','Tempat lahir','Tanggal lahir','Kelas','Status','Wali','Nomor wali'], function($out) use($students):void {
            foreach($students as $student) fputcsv($out,[$student->student_code,$student->full_name,$student->nickname,$student->birth_place,optional($student->birth_date)->format('Y-m-d'),$student->currentEnrollment?->schoolClass?->name,$student->status,$student->guardians->pluck('full_name')->join(' | '),$student->guardians->pluck('phone')->filter()->join(' | ')]);
        });
    }

    public function guardiansCsv(Request $request): StreamedResponse
    {
        $guardians=Guardian::with(['students.currentEnrollment.schoolClass'])->where('institution_id',$request->user()->institution_id)->orderBy('full_name')->get();
        return $this->csv('data-wali',['Nama','Telepon','Email','Pekerjaan','Status','Santri','Kelas'],function($out)use($guardians):void{foreach($guardians as $g)fputcsv($out,[$g->full_name,$g->phone,$g->email,$g->occupation,$g->status,$g->students->pluck('full_name')->join(' | '),$g->students->pluck('currentEnrollment.schoolClass.name')->filter()->join(' | ')]);});
    }

    public function attendanceCsv(Request $request): StreamedResponse
    {
        $records=AttendanceRecord::with(['student','meeting.schoolClass','meeting.learningGroup'])->whereHas('meeting',fn($q)=>$q->where('institution_id',$request->user()->institution_id))->latest()->get();
        return $this->csv('absensi',['Tanggal','Kelas/Kelompok','Santri','Status','Jam datang','Catatan'],function($out)use($records):void{foreach($records as $r)fputcsv($out,[optional($r->meeting?->meeting_date)->format('Y-m-d'),optional($r->meeting?->schoolClass ?? $r->meeting?->learningGroup)->name,$r->student?->full_name,$r->status,$r->arrival_time,$r->notes]);});
    }

    public function tahsinCsv(Request $request): StreamedResponse
    {
        $records=TahsinRecord::with(['student','surah'])->where('institution_id',$request->user()->institution_id)->latest()->get();
        return $this->csv('tahsin',['Tanggal','Santri','Materi','Surah','Ayat','Status','Fokus','Catatan','Tindak lanjut'],function($out)use($records):void{foreach($records as $r)fputcsv($out,[$r->created_at->format('Y-m-d'),$r->student?->full_name,$r->material_text,$r->surah?->name_latin,trim(($r->start_verse??'').'-'.($r->end_verse??''),'-'),$r->overall_status,collect($r->focus_categories)->join(' | '),$r->teacher_notes,$r->follow_up]);});
    }

    public function memorizationCsv(Request $request): StreamedResponse
    {
        $records=MemorizationRecord::with(['student','surah'])->where('institution_id',$request->user()->institution_id)->latest('recorded_at')->get();
        return $this->csv('tahfizh',['Tanggal','Santri','Jenis','Cara penyampaian','Surah','Ayat awal','Ayat akhir','Hasil','Bantuan','Prompt guru','Koreksi mandiri','Murajaah berikutnya','Tindak lanjut','Catatan'],function($out)use($records):void{foreach($records as $r)fputcsv($out,[optional($r->recorded_at)->format('Y-m-d H:i'),$r->student?->full_name,$r->record_type,$r->delivery_mode,$r->surah?->name_latin,$r->start_verse,$r->end_verse,$r->result,$r->assistance_level,$r->prompt_count,$r->self_correction_count,optional($r->next_review_date)->format('Y-m-d'),$r->follow_up,$r->teacher_notes]);});
    }

    public function murajaahCsv(Request $request): StreamedResponse
    {
        $records=MurajaahRecord::with(['student','surah'])->where('institution_id',$request->user()->institution_id)->latest('recorded_at')->get();
        return $this->csv('murajaah',['Tanggal','Santri','Jenis','Surah','Ayat awal','Ayat akhir','Hasil','Bantuan','Prompt guru','Koreksi mandiri','Review berikutnya','Rekomendasi','Catatan'],function($out)use($records):void{foreach($records as $r)fputcsv($out,[optional($r->recorded_at)->format('Y-m-d H:i'),$r->student?->full_name,$r->murajaah_type,$r->surah?->name_latin,$r->start_verse,$r->end_verse,$r->result,$r->assistance_level,$r->prompt_count,$r->self_correction_count,optional($r->next_review_date)->format('Y-m-d'),$r->review_recommendation,$r->teacher_notes]);});
    }


    public function monthlyCsv(Request $request): StreamedResponse
    {
        $institutionId = $request->user()->institution_id;
        $month = $request->string('month')->toString() ?: now()->format('Y-m');
        $start = \Carbon\Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $end = $start->copy()->endOfMonth();
        $students = Student::with('currentEnrollment.schoolClass')
            ->where('institution_id',$institutionId)->where('status','active')->orderBy('full_name')->get();

        return $this->csv('ringkasan-bulanan-'.$month,
            ['Santri','Kelas','Pertemuan','Hadir/Terlambat','Kehadiran %','Tahsīn','Tahfizh','Murāja‘ah','Tugas Selesai'],
            function($out) use($students,$start,$end): void {
                foreach($students as $student){
                    $attendance=$student->attendanceRecords()->whereHas('meeting',fn($q)=>$q->whereBetween('meeting_date',[$start,$end]))->get();
                    $meetings=$attendance->pluck('meeting_id')->unique()->count();
                    $present=$attendance->whereIn('status',['present','late'])->count();
                    $pct=$attendance->count()>0?round(($present/$attendance->count())*100):0;
                    $tahsin=$student->tahsinRecords()->whereBetween('created_at',[$start,$end])->count();
                    $hifz=$student->memorizationRecords()->whereBetween('recorded_at',[$start,$end])->count();
                    $murajaah=$student->murajaahRecords()->whereBetween('recorded_at',[$start,$end])->count();
                    $tasks=AssignmentRecipient::where('student_id',$student->id)->where('status','completed')->whereBetween('completed_at',[$start,$end])->count();
                    fputcsv($out,[$student->full_name,$student->currentEnrollment?->schoolClass?->name,$meetings,$present,$pct.'%',$tahsin,$hifz,$murajaah,$tasks]);
                }
            }
        );
    }

    public function tasksCsv(Request $request): StreamedResponse
    {
        $records=AssignmentRecipient::with(['student','assignment.schoolClass','assignment.learningGroup'])
            ->whereHas('assignment',fn($q)=>$q->where('institution_id',$request->user()->institution_id))
            ->latest()->get();
        return $this->csv('tugas-santri',['Tugas','Jenis','Kelas/Kelompok','Santri','Status','Tenggat','Selesai'],function($out)use($records):void{
            foreach($records as $r)fputcsv($out,[$r->assignment?->title,$r->assignment?->assignment_type,optional($r->assignment?->schoolClass ?? $r->assignment?->learningGroup)->name,$r->student?->full_name,$r->status,optional($r->assignment?->due_at)->format('Y-m-d H:i'),optional($r->completed_at)->format('Y-m-d H:i')]);
        });
    }

    private function csv(string $name,array $headers,callable $rows): StreamedResponse
    {
        return response()->streamDownload(function()use($headers,$rows):void{$out=fopen('php://output','w');fwrite($out,"\xEF\xBB\xBF");fputcsv($out,$headers);$rows($out);fclose($out);},$name.'-'.now()->format('Ymd-His').'.csv',['Content-Type'=>'text/csv; charset=UTF-8']);
    }
}
