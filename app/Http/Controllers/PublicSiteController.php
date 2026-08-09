<?php

namespace App\Http\Controllers;

/** @phase 4.2 Brand & Universal Home */

use App\Models\AdmissionRegistration;
use App\Models\Institution;
use App\Models\PublicArticle;
use App\Models\PublicPage;
use App\Support\Feature;
use App\Support\InstitutionReference;
use App\Support\StudentPledge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
    public function home(Request $request): View|RedirectResponse
    {
        $this->ensurePublicFeature('public_website');
        if ($request->getHost() === config('sullam.portal_host')) {
            return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
        }

        $institutionId = $this->publicInstitutionId();
        $featuredArticles = Schema::hasTable('public_articles')
            ? PublicArticle::query()->where('institution_id', $institutionId)->where('status','published')->where(fn($q)=>$q->whereNull('published_at')->orWhere('published_at','<=',now()))->latest('published_at')->limit(3)->get()
            : collect();
        return view('public.home', compact('featuredArticles'));
    }

    public function page(string $slug): View
    {
        $this->ensurePublicFeature('public_website');
        $page = Schema::hasTable('public_pages') ? PublicPage::where('institution_id', $this->publicInstitutionId())->where('slug',$slug)->where('status','published')->first() : null;

        if (! $page) {
            $view = match ($slug) {
                'tentang' => 'public.about',
                'program' => 'public.programs',
                'tpa' => 'public.tpa',
                'academy' => 'public.academy',
                'kontak' => 'public.contact',
                'privasi' => 'public.privacy',
                'syarat-ketentuan' => 'public.terms',
                default => null,
            };

            abort_unless($view && view()->exists($view), 404);
            return view($view);
        }

        return view('public.page', compact('page'));
    }

    public function solution(string $audience): View
    {
        $this->ensurePublicFeature('public_website');

        $solutions = [
            'personal' => [
                'eyebrow' => 'UNTUK PERSONAL',
                'title' => 'Ruang privat untuk perjalanan Al-Qur’an Anda sendiri.',
                'lead' => 'Tetapkan ritme, catat aktivitas, jaga murāja‘ah, dan pilih kapan Anda ingin belajar mandiri atau meminta pendampingan.',
                'actors' => 'Personal',
                'features' => ['Target dan jurnal pribadi', 'Latihan Qur’an dan Qur’an Journey', 'Kontrol privasi dan persetujuan', 'Dapat ditingkatkan ke bimbingan ustadz'],
                'cta' => 'Buat Ruang Personal',
                'url' => route('personal.register'),
            ],
            'ustadz' => [
                'eyebrow' => 'BIMBINGAN USTADZ',
                'title' => 'Pendampingan personal–ustadz tanpa harus terikat lembaga.',
                'lead' => 'Satu ruang kerja untuk target bersama, setoran, koreksi, jadwal, dan evaluasi privat dengan batas akses yang jelas.',
                'actors' => 'Personal ↔ Ustadz',
                'features' => ['Program dan target bimbingan', 'Setoran audio, video, atau teks', 'Koreksi per ayat', 'Riwayat tetap milik pembelajar'],
                'cta' => 'Pelajari alurnya',
                'url' => route('public.contact'),
            ],
            'keluarga' => [
                'eyebrow' => 'UNTUK KELUARGA',
                'title' => 'Orang tua mendampingi tanpa menjadikan rumah ruang tekanan kedua.',
                'lead' => 'Pantau yang memang diizinkan, bantu rutinitas di rumah, dan tetap hormati ruang pribadi serta tahap perkembangan anak.',
                'actors' => 'Orang Tua ↔ Anak',
                'features' => ['Beberapa anak dalam satu akun wali', 'Tugas dan rutinitas rumah', 'Laporan perkembangan', 'Persetujuan sesuai usia dan konteks'],
                'cta' => 'Diskusikan kebutuhan keluarga',
                'url' => route('public.contact'),
            ],
            'lembaga' => [
                'eyebrow' => 'UNTUK LEMBAGA',
                'title' => 'Satu fondasi untuk TPA, sekolah, pesantren, kampus, dan komunitas.',
                'lead' => 'Kelola pembelajaran, pengajar, peserta, keluarga, laporan, serta komunikasi dengan istilah dan struktur yang mengikuti lembaga Anda.',
                'actors' => 'Lembaga ↔ Pengajar ↔ Peserta ↔ Keluarga',
                'features' => ['Multi-cabang dan tahun ajaran', 'Kelas, kelompok, jadwal, dan presensi', 'Tahsin, tahfiz, murāja‘ah, dan rapor', 'Branding dan istilah per jenis lembaga'],
                'cta' => 'Daftarkan Lembaga',
                'url' => route('institution.register'),
            ],
        ];

        abort_unless(isset($solutions[$audience]), 404);

        return view('public.solution', ['solution' => $solutions[$audience], 'audience' => $audience]);
    }

    public function features(): View
    {
        $this->ensurePublicFeature('public_website');

        return view('public.features');
    }

    public function pricing(): View
    {
        $this->ensurePublicFeature('public_website');

        return view('public.pricing');
    }

    public function pledge(): View
    {
        $this->ensurePublicFeature('public_website');
        $institutionId = $this->publicInstitutionId();

        return view('public.pledge', [
            'pledge' => StudentPledge::forInstitution($institutionId),
        ]);
    }


    public function institutionShowcase(): View
    {
        $this->ensurePublicFeature('public_website');
        return view('public.institution-showcase', [
            'profile' => InstitutionReference::current(),
        ]);
    }

    public function institutionReference(): View
    {
        $this->ensurePublicFeature('public_website');
        return view('public.institution-reference', [
            'profile' => InstitutionReference::current(),
        ]);
    }

    public function articles(): View
    {
        $this->ensurePublicFeature('public_website');
        if (! Schema::hasTable('public_articles')) return view('public.articles');
        $articles = PublicArticle::query()
            ->where('institution_id', $this->publicInstitutionId())
            ->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->latest('published_at')
            ->paginate(12);
        return view('public.articles', compact('articles'));
    }

    public function article(string $article): View
    {
        $this->ensurePublicFeature('public_website');
        abort_unless(Schema::hasTable('public_articles'),404);
        $article=PublicArticle::where('institution_id', $this->publicInstitutionId())->where('slug',$article)->firstOrFail();
        abort_unless($article->status === 'published' && (! $article->published_at || $article->published_at->isPast()),404);
        return view('public.article',compact('article'));
    }

    public function registration(): View
    {
        $institutionId = $this->publicInstitutionId();
        return view('public.registration', [
            'admissionsEnabled' => Feature::enabled('admissions', $institutionId, true),
        ]);
    }

    public function storeRegistration(Request $request): RedirectResponse
    {
        $this->ensurePublicFeature('admissions');
        $data = $request->validate([
            'student_name' => ['required','string','max:190'],
            'student_age' => ['nullable','string','max:30'],
            'guardian_name' => ['required','string','max:190'],
            'guardian_phone' => ['required','string','max:30'],
            'guardian_email' => ['nullable','email','max:190'],
            'desired_program' => ['nullable','string','max:100'],
            'notes' => ['nullable','string','max:3000'],
        ]);

        abort_unless(Schema::hasTable('admission_registrations'),503,'Pendaftaran sedang disiapkan.');
        $institutionId = $this->publicInstitutionId();
        AdmissionRegistration::create([...$data, 'institution_id' => $institutionId, 'status' => 'new']);

        return back()->with('success', 'Pendaftaran awal telah diterima. Tim kami akan menghubungi wali melalui nomor yang dicantumkan.');
    }
    private function ensurePublicFeature(string $key): void
    {
        $institutionId = $this->publicInstitutionId();
        abort_unless(Feature::enabled($key, $institutionId, true), 404);
    }

    private function publicInstitutionId(): ?int
    {
        if (! Schema::hasTable('institutions')) {
            return null;
        }

        $code = (string) config('sullam.initial_institution_code', 'ALINSYIRAH');

        return Institution::query()
            ->where('status', 'active')
            ->where('code', $code)
            ->value('id')
            ?? Institution::query()->where('status', 'active')->orderBy('id')->value('id');
    }

}
