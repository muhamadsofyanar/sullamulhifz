<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\MemorizationRecord;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $institutionId = $request->user()->institution_id;

        return view('admin.reports.index', [
            'studentCount' => Student::where('institution_id', $institutionId)->where('status', 'active')->count(),
            'attendanceCount' => AttendanceRecord::whereHas('meeting', fn ($q) => $q->where('institution_id', $institutionId))->count(),
            'memorizationCount' => MemorizationRecord::where('institution_id', $institutionId)->count(),
        ]);
    }

    public function studentsCsv(Request $request): StreamedResponse
    {
        $students = Student::with(['currentEnrollment.schoolClass', 'guardians'])
            ->where('institution_id', $request->user()->institution_id)
            ->orderBy('full_name')->get();

        return response()->streamDownload(function () use ($students): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Kode', 'Nama', 'Nama Panggilan', 'Kelas Aktif', 'Status', 'Wali', 'Nomor Wali']);
            foreach ($students as $student) {
                fputcsv($out, [
                    $student->student_code,
                    $student->full_name,
                    $student->nickname,
                    $student->currentEnrollment?->schoolClass?->name,
                    $student->status,
                    $student->guardians->pluck('full_name')->join(' | '),
                    $student->guardians->pluck('phone')->filter()->join(' | '),
                ]);
            }
            fclose($out);
        }, 'data-santri-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function attendanceCsv(Request $request): StreamedResponse
    {
        $records = AttendanceRecord::with(['student', 'meeting.schoolClass', 'meeting.learningGroup'])
            ->whereHas('meeting', fn ($q) => $q->where('institution_id', $request->user()->institution_id))
            ->latest()->get();

        return response()->streamDownload(function () use ($records): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Tanggal', 'Kelas/Kelompok', 'Santri', 'Status', 'Catatan']);
            foreach ($records as $record) {
                fputcsv($out, [
                    $record->meeting?->meeting_date?->format('Y-m-d'),
                    ($record->meeting?->schoolClass ?: $record->meeting?->learningGroup)?->name,
                    $record->student?->full_name,
                    $record->status,
                    $record->notes,
                ]);
            }
            fclose($out);
        }, 'absensi-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
