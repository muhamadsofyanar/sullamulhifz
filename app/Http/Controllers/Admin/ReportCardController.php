<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\AssignmentRecipient;
use App\Models\ReportCard;
use App\Models\ReportCardItem;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReportCardController extends Controller
{
    public function index(Request $request): View
    {
        $institutionId = $request->user()->institution_id;
        return view('admin.report-cards.index', [
            'cards' => ReportCard::with(['student.currentEnrollment.schoolClass','academicYear'])
                ->where('institution_id', $institutionId)
                ->latest()->paginate(20),
            'students' => Student::with('currentEnrollment.schoolClass')
                ->where('institution_id', $institutionId)->where('status','active')->orderBy('full_name')->get(),
            'year' => AcademicYear::where('institution_id', $institutionId)->where('is_active', true)->first(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $institutionId = $request->user()->institution_id;
        $data = $request->validate([
            'student_id' => ['required', Rule::exists('students','id')->where('institution_id',$institutionId)],
            'academic_year_id' => ['required', Rule::exists('academic_years','id')->where('institution_id',$institutionId)],
            'semester' => ['required', Rule::in(['ganjil','genap'])],
            'period_start' => ['nullable','date'],
            'period_end' => ['nullable','date','after_or_equal:period_start'],
        ]);

        $student = Student::where('institution_id', $institutionId)->findOrFail($data['student_id']);
        $card = DB::transaction(function () use ($request, $data, $student, $institutionId): ReportCard {
            $card = ReportCard::updateOrCreate(
                ['academic_year_id'=>$data['academic_year_id'],'student_id'=>$student->id,'semester'=>$data['semester']],
                [...$data,'institution_id'=>$institutionId,'prepared_by_user_id'=>$request->user()->id,'status'=>'draft']
            );
            $this->refreshItems($card, $student);
            return $card;
        });

        return redirect()->route('admin.report-cards.show', $card)->with('success', 'Rapor berhasil disiapkan dari catatan pembelajaran yang tersedia.');
    }

    public function show(Request $request, ReportCard $reportCard): View
    {
        $this->guardInstitution($request, $reportCard);
        $reportCard->load(['student.currentEnrollment.schoolClass','student.guardians','academicYear','items','preparedBy']);
        return view('admin.report-cards.show', compact('reportCard'));
    }

    public function update(Request $request, ReportCard $reportCard): RedirectResponse
    {
        $this->guardInstitution($request, $reportCard);
        $data = $request->validate([
            'teacher_summary' => ['nullable','string','max:5000'],
            'guardian_note' => ['nullable','string','max:5000'],
            'items' => ['nullable','array'],
            'items.*.id' => ['required','integer','exists:report_card_items,id'],
            'items.*.score' => ['nullable','string','max:50'],
            'items.*.description' => ['nullable','string','max:2000'],
        ]);

        DB::transaction(function () use ($reportCard, $data): void {
            $reportCard->update([
                'teacher_summary' => $data['teacher_summary'] ?? null,
                'guardian_note' => $data['guardian_note'] ?? null,
            ]);
            foreach ($data['items'] ?? [] as $item) {
                ReportCardItem::where('report_card_id', $reportCard->id)->whereKey($item['id'])->update([
                    'score' => $item['score'] ?? null,
                    'description' => $item['description'] ?? null,
                ]);
            }
        });

        return back()->with('success', 'Rapor berhasil diperbarui.');
    }

    public function publish(Request $request, ReportCard $reportCard): RedirectResponse
    {
        $this->guardInstitution($request, $reportCard);
        $reportCard->update(['status'=>'published','published_at'=>now()]);
        return back()->with('success', 'Rapor telah diterbitkan dan dapat dilihat wali.');
    }

    public function print(Request $request, ReportCard $reportCard): View
    {
        $this->guardInstitution($request, $reportCard);
        $reportCard->load(['student.currentEnrollment.schoolClass','student.guardians','academicYear','items','preparedBy']);
        return view('admin.report-cards.print', compact('reportCard'));
    }

    private function refreshItems(ReportCard $card, Student $student): void
    {
        $attendance = $student->attendanceRecords()
            ->when($card->period_start, fn ($q) => $q->whereHas('meeting', fn ($m) => $m->whereDate('meeting_date','>=',$card->period_start)))
            ->when($card->period_end, fn ($q) => $q->whereHas('meeting', fn ($m) => $m->whereDate('meeting_date','<=',$card->period_end)))
            ->get();
        $present = $attendance->whereIn('status',['present','late'])->count();
        $total = $attendance->count();
        $attendancePct = $total > 0 ? round(($present / $total) * 100) : null;

        $period = fn ($query, string $column = 'created_at') => $query
            ->when($card->period_start, fn ($q) => $q->whereDate($column, '>=', $card->period_start))
            ->when($card->period_end, fn ($q) => $q->whereDate($column, '<=', $card->period_end));

        $latestTahsin = $period($student->tahsinRecords())->latest()->first();
        $latestHifz = $period($student->memorizationRecords()->with('surah'), 'recorded_at')->latest('recorded_at')->first();
        $latestMurajaah = $period($student->murajaahRecords()->with('surah'), 'recorded_at')->latest('recorded_at')->first();
        $taskQuery = AssignmentRecipient::where('student_id', $student->id);
        $period($taskQuery, 'completed_at');
        $completedTasks = $taskQuery->where('status', 'completed')->count();

        $tahsinLabels = ['good'=>'Baik','practice_needed'=>'Perlu latihan','guidance_needed'=>'Perlu pendampingan','special_correction'=>'Perlu koreksi khusus'];
        $hifzLabels = ['fluent'=>'Lancar','fair'=>'Lulus dengan penguatan','repeat_needed'=>'Perlu diulang','postponed'=>'Belum dinilai'];
        $murajaahLabels = ['maintained'=>'Terjaga','strengthening_needed'=>'Perlu penguatan','reactivation_needed'=>'Perlu dipanggil kembali'];

        $items = [
            ['category'=>'kehadiran','label'=>'Kehadiran','score'=>$attendancePct !== null ? $attendancePct.'%' : '-', 'description'=>$total.' catatan kehadiran; '.$present.' hadir atau terlambat.','sort_order'=>10],
            ['category'=>'tahsin','label'=>'Tahsīn','score'=>$latestTahsin ? ($tahsinLabels[$latestTahsin->overall_status] ?? $latestTahsin->overall_status) : '-', 'description'=>$latestTahsin?->teacher_notes ?: 'Belum ada penilaian Tahsīn pada periode ini.','sort_order'=>20],
            ['category'=>'tahfizh','label'=>'Tahfizh','score'=>$latestHifz ? ($hifzLabels[$latestHifz->result] ?? $latestHifz->result) : '-', 'description'=>$latestHifz ? (($latestHifz->surah?->name_latin ?: 'Surah').' ayat '.$latestHifz->start_verse.'–'.$latestHifz->end_verse.'. '.($latestHifz->review_recommendation ?: $latestHifz->teacher_notes)) : 'Belum ada setoran hafalan pada periode ini.','sort_order'=>30],
            ['category'=>'murajaah','label'=>'Murāja‘ah','score'=>$latestMurajaah ? ($murajaahLabels[$latestMurajaah->result] ?? $latestMurajaah->result) : '-', 'description'=>$latestMurajaah ? (($latestMurajaah->surah?->name_latin ?: 'Surah').' ayat '.$latestMurajaah->start_verse.'–'.$latestMurajaah->end_verse.'. '.($latestMurajaah->review_recommendation ?: $latestMurajaah->teacher_notes)) : 'Belum ada catatan Murāja‘ah pada periode ini.','sort_order'=>40],
            ['category'=>'tugas','label'=>'Tugas Rumah','score'=>(string)$completedTasks, 'description'=>$completedTasks.' tugas selesai pada periode rapor.','sort_order'=>45],
            ['category'=>'pembiasaan','label'=>'Adab & Pembiasaan','score'=>'-', 'description'=>'Diisi guru berdasarkan pengamatan nyata, tanpa ranking atau perbandingan.','sort_order'=>50],
        ];

        foreach ($items as $item) {
            ReportCardItem::updateOrCreate(
                ['report_card_id'=>$card->id,'category'=>$item['category'],'label'=>$item['label']],
                $item
            );
        }
    }

    private function guardInstitution(Request $request, ReportCard $card): void
    {
        abort_unless($card->institution_id === $request->user()->institution_id, 404);
    }
}
