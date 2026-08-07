<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\AcademicYear;
use App\Models\Branch;
use App\Models\FeatureFlag;
use App\Models\Institution;
use App\Services\RoadmapStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PlatformController extends Controller
{
    public function index(Request $request): View
    {
        $institutionId = (int) $request->user()->institution_id;

        return view('admin.platform.index', [
            'branches' => Branch::query()->where('institution_id', $institutionId)->orderByDesc('is_main')->orderBy('name')->get(),
            'years' => AcademicYear::query()->where('institution_id', $institutionId)->with('periods')->orderByDesc('start_date')->get(),
            'features' => FeatureFlag::query()->where('institution_id', $institutionId)->orderBy('feature_key')->get(),
            'featureCatalog' => $this->featureCatalog(),
            'roadmapPhases' => app(RoadmapStatusService::class)->phases(Institution::query()->findOrFail($institutionId)),
        ]);
    }

    public function storeBranch(Request $request): RedirectResponse
    {
        $institutionId = (int) $request->user()->institution_id;
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9_-]+$/', Rule::unique('branches')->where('institution_id', $institutionId)],
            'address' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:40'],
        ]);

        Branch::create($data + [
            'institution_id' => $institutionId,
            'status' => 'active',
            'is_main' => false,
        ]);

        return back()->with('success', 'Cabang berhasil ditambahkan. Data ini siap digunakan ketika mode multi-cabang diaktifkan.');
    }

    public function updateBranch(Request $request, Branch $branch): RedirectResponse
    {
        abort_unless($branch->institution_id === $request->user()->institution_id, 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:40'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'is_main' => ['nullable', 'boolean'],
        ]);

        $makeMain = (bool) ($data['is_main'] ?? false);
        if ($makeMain) {
            Branch::query()->where('institution_id', $branch->institution_id)->whereKeyNot($branch->id)->update(['is_main' => false]);
            $data['status'] = 'active';
        }

        $branch->update($data + ['is_main' => $makeMain]);

        if (! Branch::query()->where('institution_id', $branch->institution_id)->where('status', 'active')->where('is_main', true)->exists()) {
            $replacement = Branch::query()->where('institution_id', $branch->institution_id)->where('status', 'active')->orderBy('id')->first();
            if ($replacement) {
                $replacement->update(['is_main' => true]);
            } else {
                $branch->update(['status' => 'active', 'is_main' => true]);
            }
        }

        return back()->with('success', 'Cabang diperbarui.');
    }

    public function storePeriod(Request $request): RedirectResponse
    {
        $institutionId = (int) $request->user()->institution_id;
        $data = $request->validate([
            'academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('institution_id', $institutionId)],
            'name' => ['required', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'status' => ['required', Rule::in(['draft', 'active', 'closed'])],
        ]);

        AcademicPeriod::updateOrCreate(
            ['academic_year_id' => $data['academic_year_id'], 'name' => $data['name']],
            $data,
        );

        return back()->with('success', 'Periode akademik berhasil disimpan.');
    }

    public function updateFeature(Request $request, FeatureFlag $featureFlag): RedirectResponse
    {
        abort_unless($featureFlag->institution_id === $request->user()->institution_id, 404);
        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
        ]);
        $featureFlag->update(['enabled' => (bool) ($data['enabled'] ?? false)]);
        Cache::forget('sullam:feature:'.$featureFlag->institution_id.':'.$featureFlag->feature_key);

        return back()->with('success', 'Status modul berhasil diperbarui tanpa redeploy.');
    }

    private function featureCatalog(): array
    {
        return [
            'core_academic' => ['Fondasi akademik', 'Tahun ajaran, kelas, jadwal, pertemuan dan pembelajaran inti.'],
            'academy_portal' => ['Portal Academy', 'Portal LMS mandiri pada academy.sullamulhifz.or.id.'],
            'quran_audio' => ['Full Qur’an Learning', 'Mushaf 30 juz, dua qari, playlist, repeat, preset, bookmark dan riwayat baca.'],
            'parent_academy' => ['Parent Academy / LMS', 'Materi, modul, video dan progres keluarga.'],
            'teacher_academy' => ['Teacher Academy', 'Microlearning dan pengembangan kompetensi guru.'],
            'stifin_learning' => ['STIFIn Learning', 'STIFIn sebagai informasi pendamping, bukan label atau penentu kemampuan.'],
            'family_learning' => ['Family Learning', 'Aktivitas keluarga, parenting, komunikasi, dan pendampingan rumah.'],
            'learning_paths' => ['Jalur Belajar', 'Urutan materi dan latihan agar Academy tidak menjadi katalog yang membingungkan.'],
            'academy_reflections' => ['Refleksi Academy', 'Catatan pribadi setelah materi dan tindak lanjut kecil.'],
            'character_talent' => ['Character & Talent', 'Public speaking, kreativitas, olahraga, kerja sama, dan pembinaan karakter.'],
            'student_portfolio' => ['Portofolio Anak', 'Jejak karya dan perkembangan yang bermakna tanpa ranking.'],
            'learning_insights' => ['Learning Insight', 'Ringkasan berbasis bukti untuk membantu keputusan guru dan keluarga.'],
            'public_website' => ['Website publik', 'Halaman, artikel dan identitas lembaga.'],
            'report_cards' => ['Rapor perkembangan', 'Penyusunan dan publikasi rapor santri.'],
            'admissions' => ['Pendaftaran santri', 'Form pendaftaran dan tindak lanjut admin.'],
            'api_integrations' => ['API & Integrasi', 'Fondasi API, email, WhatsApp, object storage, dan integrasi layanan.'],
            'community' => ['Community terbatas', 'Ruang komunitas bermoderasi; nonaktif sampai kebijakan dan moderasi siap.'],
            'ai_assist' => ['AI Assist', 'Asisten penyusunan draft/insight; tetap nonaktif sampai tata kelola dan validasi siap.'],
            'payments' => ['Pembayaran', 'Fondasi pembayaran opsional untuk fase ekspansi.'],
            'multi_branch' => ['Multi-cabang', 'Mengaktifkan penggunaan cabang pada operasional harian.'],
            'multi_institution' => ['Multi-lembaga', 'Ekspansi SaaS/lembaga lain setelah fondasi tenant diuji penuh.'],
        ];
    }

}
