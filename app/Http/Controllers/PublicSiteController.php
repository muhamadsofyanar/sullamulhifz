<?php

namespace App\Http\Controllers;

use App\Models\AdmissionRegistration;
use App\Models\Institution;
use App\Models\PublicArticle;
use App\Models\PublicPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class PublicSiteController extends Controller
{
    public function home(Request $request): View|RedirectResponse
    {
        if ($request->getHost() === config('sullam.portal_host')) {
            return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
        }

        $featuredArticles = Schema::hasTable('public_articles')
            ? PublicArticle::query()->where('status','published')->where(fn($q)=>$q->whereNull('published_at')->orWhere('published_at','<=',now()))->latest('published_at')->limit(3)->get()
            : collect();
        return view('public.home', compact('featuredArticles'));
    }

    public function page(string $slug): View
    {
        $page = Schema::hasTable('public_pages') ? PublicPage::where('slug',$slug)->where('status','published')->first() : null;

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

    public function articles(): View
    {
        if (! Schema::hasTable('public_articles')) return view('public.articles');
        $articles = PublicArticle::query()
            ->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->latest('published_at')
            ->paginate(12);
        return view('public.articles', compact('articles'));
    }

    public function article(string $article): View
    {
        abort_unless(Schema::hasTable('public_articles'),404);
        $article=PublicArticle::where('slug',$article)->firstOrFail();
        abort_unless($article->status === 'published' && (! $article->published_at || $article->published_at->isPast()),404);
        return view('public.article',compact('article'));
    }

    public function registration(): View
    {
        return view('public.registration');
    }

    public function storeRegistration(Request $request): RedirectResponse
    {
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
        $institutionId = Institution::where('status', 'active')->value('id');
        AdmissionRegistration::create([...$data, 'institution_id' => $institutionId, 'status' => 'new']);

        return back()->with('success', 'Pendaftaran awal telah diterima. Tim kami akan menghubungi wali melalui nomor yang dicantumkan.');
    }
}
