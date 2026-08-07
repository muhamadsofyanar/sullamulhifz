<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdmissionRegistration;
use App\Models\PublicArticle;
use App\Models\PublicPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    public function index(Request $request): View
    {
        $institutionId = $request->user()->institution_id;

        return view('admin.website.index', [
            'pages' => PublicPage::where('institution_id', $institutionId)->orderBy('sort_order')->orderBy('title')->get(),
            'articles' => PublicArticle::where('institution_id', $institutionId)->latest()->paginate(10, ['*'], 'articles_page'),
            'registrations' => AdmissionRegistration::where('institution_id', $institutionId)->latest()->paginate(10, ['*'], 'registrations_page'),
        ]);
    }

    public function storePage(Request $request): RedirectResponse
    {
        $institutionId = $request->user()->institution_id;
        $data = $request->validate([
            'slug' => ['required', 'alpha_dash', 'max:120', Rule::unique('public_pages', 'slug')->where('institution_id', $institutionId)],
            'title' => ['required', 'string', 'max:190'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string', 'max:100000'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        PublicPage::create([
            ...$data,
            'institution_id' => $institutionId,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return back()->with('success', 'Halaman publik berhasil ditambahkan.');
    }

    public function updatePage(Request $request, PublicPage $page): RedirectResponse
    {
        $this->authorizeTenant($request, $page->institution_id);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'summary' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string', 'max:100000'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);
        $page->update($data);

        return back()->with('success', 'Halaman publik berhasil diperbarui.');
    }

    public function storeArticle(Request $request): RedirectResponse
    {
        $institutionId = $request->user()->institution_id;
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'alpha_dash', 'max:190', Rule::unique('public_articles', 'slug')->where('institution_id', $institutionId)],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string', 'max:150000'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'cover_image' => ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(3072)],
        ]);

        $cover = $request->hasFile('cover_image')
            ? $request->file('cover_image')->store('public/articles/'.$institutionId, 'public')
            : null;

        PublicArticle::create([
            ...collect($data)->except('cover_image')->all(),
            'institution_id' => $institutionId,
            'author_user_id' => $request->user()->id,
            'slug' => $data['slug'] ?: $this->uniqueArticleSlug($institutionId, $data['title']),
            'cover_image_path' => $cover,
            'published_at' => $data['status'] === 'published' ? ($data['published_at'] ?: now()) : null,
        ]);

        return back()->with('success', 'Artikel berhasil disimpan.');
    }

    public function updateArticle(Request $request, PublicArticle $article): RedirectResponse
    {
        $this->authorizeTenant($request, $article->institution_id);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string', 'max:150000'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'published_at' => ['nullable', 'date'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'cover_image' => ['nullable', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max(3072)],
        ]);

        $payload = collect($data)->except('cover_image')->all();
        if ($request->hasFile('cover_image')) {
            $oldCover = $article->cover_image_path;
            $payload['cover_image_path'] = $request->file('cover_image')->store('public/articles/'.$article->institution_id, 'public');
            if ($oldCover) {
                Storage::disk('public')->delete($oldCover);
            }
        }
        if ($data['status'] === 'published' && ! $article->published_at) {
            $payload['published_at'] = $data['published_at'] ?: now();
        }
        if ($data['status'] === 'draft') {
            $payload['published_at'] = null;
        }
        $article->update($payload);

        return back()->with('success', 'Artikel berhasil diperbarui.');
    }

    public function updateRegistration(Request $request, AdmissionRegistration $registration): RedirectResponse
    {
        $this->authorizeTenant($request, $registration->institution_id);
        $data = $request->validate([
            'status' => ['required', Rule::in(['new', 'contacted', 'scheduled', 'accepted', 'rejected', 'closed'])],
        ]);
        $registration->update([
            'status' => $data['status'],
            'handled_by_user_id' => $request->user()->id,
            'handled_at' => now(),
        ]);

        return back()->with('success', 'Status pendaftaran berhasil diperbarui.');
    }


    private function uniqueArticleSlug(int $institutionId, string $title): string
    {
        $base = Str::slug($title) ?: 'artikel';
        $slug = $base;
        $counter = 2;

        while (PublicArticle::query()->where('institution_id', $institutionId)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return Str::limit($slug, 190, '');
    }

    private function authorizeTenant(Request $request, ?int $institutionId): void
    {
        $user = $request->user();
        abort_unless(
            ($user->hasRole('superadmin') && $user->institution_id === null)
            || (int) $institutionId === (int) $user->institution_id,
            404,
        );
    }
}
