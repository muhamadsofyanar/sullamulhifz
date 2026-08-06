<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdmissionRegistration;
use App\Models\PublicArticle;
use App\Models\PublicPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.website.index', [
            'pages' => PublicPage::orderBy('sort_order')->orderBy('title')->get(),
            'articles' => PublicArticle::latest()->paginate(10, ['*'], 'articles_page'),
            'registrations' => AdmissionRegistration::latest()->paginate(10, ['*'], 'registrations_page'),
        ]);
    }

    public function storePage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'slug' => ['required','alpha_dash','max:120', Rule::unique('public_pages','slug')],
            'title' => ['required','string','max:190'],
            'summary' => ['nullable','string','max:1000'],
            'content' => ['nullable','string'],
            'status' => ['required', Rule::in(['draft','published'])],
            'seo_title' => ['nullable','string','max:190'],
            'seo_description' => ['nullable','string','max:500'],
            'sort_order' => ['nullable','integer','min:0','max:999'],
        ]);

        PublicPage::create([
            ...$data,
            'institution_id' => $request->user()->institution_id,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return back()->with('success', 'Halaman publik berhasil ditambahkan.');
    }

    public function updatePage(Request $request, PublicPage $page): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required','string','max:190'],
            'summary' => ['nullable','string','max:1000'],
            'content' => ['nullable','string'],
            'status' => ['required', Rule::in(['draft','published'])],
            'seo_title' => ['nullable','string','max:190'],
            'seo_description' => ['nullable','string','max:500'],
            'sort_order' => ['nullable','integer','min:0','max:999'],
        ]);
        $page->update($data);
        return back()->with('success', 'Halaman publik berhasil diperbarui.');
    }

    public function storeArticle(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required','string','max:190'],
            'slug' => ['nullable','alpha_dash','max:190', Rule::unique('public_articles','slug')],
            'excerpt' => ['nullable','string','max:1000'],
            'content' => ['required','string'],
            'status' => ['required', Rule::in(['draft','published'])],
            'published_at' => ['nullable','date'],
            'seo_title' => ['nullable','string','max:190'],
            'seo_description' => ['nullable','string','max:500'],
            'cover_image' => ['nullable','image','max:3072'],
        ]);

        $cover = $request->hasFile('cover_image')
            ? $request->file('cover_image')->store('public/articles', 'public')
            : null;

        PublicArticle::create([
            ...collect($data)->except('cover_image')->all(),
            'institution_id' => $request->user()->institution_id,
            'author_user_id' => $request->user()->id,
            'slug' => $data['slug'] ?: Str::slug($data['title']).'-'.Str::lower(Str::random(5)),
            'cover_image_path' => $cover,
            'published_at' => $data['status'] === 'published' ? ($data['published_at'] ?: now()) : null,
        ]);

        return back()->with('success', 'Artikel berhasil disimpan.');
    }

    public function updateArticle(Request $request, PublicArticle $article): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required','string','max:190'],
            'excerpt' => ['nullable','string','max:1000'],
            'content' => ['required','string'],
            'status' => ['required', Rule::in(['draft','published'])],
            'published_at' => ['nullable','date'],
            'seo_title' => ['nullable','string','max:190'],
            'seo_description' => ['nullable','string','max:500'],
            'cover_image' => ['nullable','image','max:3072'],
        ]);

        $payload = collect($data)->except('cover_image')->all();
        if ($request->hasFile('cover_image')) {
            $payload['cover_image_path'] = $request->file('cover_image')->store('public/articles', 'public');
        }
        if ($data['status'] === 'published' && ! $article->published_at) {
            $payload['published_at'] = $data['published_at'] ?: now();
        }
        $article->update($payload);

        return back()->with('success', 'Artikel berhasil diperbarui.');
    }

    public function updateRegistration(Request $request, AdmissionRegistration $registration): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['new','contacted','scheduled','accepted','rejected','closed'])]]);
        $registration->update([
            'status' => $data['status'],
            'handled_by_user_id' => $request->user()->id,
            'handled_at' => now(),
        ]);

        return back()->with('success', 'Status pendaftaran berhasil diperbarui.');
    }
}
