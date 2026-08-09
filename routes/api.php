<?php

use App\Models\AcademyProgram;
use App\Http\Controllers\CommunicationWebhookController;
use App\Models\Institution;
use Illuminate\Support\Facades\Route;

Route::post('/v1/webhooks/communications/whatsapp/{connection}', [CommunicationWebhookController::class, 'whatsapp'])
    ->middleware('throttle:120,1')
    ->name('api.webhooks.communications.whatsapp');

Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'product' => 'Sullamul Hifz',
    'release' => trim((string) @file_get_contents(base_path('RELEASE'))) ?: 'unknown',
    'time' => now()->toIso8601String(),
]))->name('api.health');

Route::get('/v1/meta', fn () => response()->json([
    'product' => 'Sullamul Hifz',
    'tagline' => 'Bukan Sekadar Hafal, Tapi KUAT',
    'api_version' => 'v1',
    'architecture' => [
        'website' => config('sullam.public_url'),
        'app' => config('sullam.portal_base_url'),
        'academy' => config('sullam.academy_portal_url'),
        'api' => 'https://'.config('sullam.api_host'),
    ],
    'documentation_status' => 'starter',
]))->name('api.meta');

Route::get('/v1/academy-preview', function () {
    $institution = Institution::query()->where('status', 'active')->orderBy('id')->first();
    if (! $institution) {
        return response()->json(['data' => [], 'note' => 'Belum ada lembaga aktif.']);
    }

    $programs = AcademyProgram::query()
        ->withCount(['modules'])
        ->with(['modules.lessons' => fn ($query) => $query->where('status', 'published')])
        ->where('institution_id', $institution->id)
        ->where('status', 'published')
        ->orderByDesc('is_featured')
        ->orderBy('sort_order')
        ->get()
        ->map(fn (AcademyProgram $program) => [
            'title' => $program->title,
            'slug' => $program->slug,
            'audience' => $program->audience,
            'summary' => $program->summary,
            'modules' => $program->modules_count,
            'lessons' => $program->modules->sum(fn ($module) => $module->lessons->count()),
            'featured' => (bool) $program->is_featured,
        ]);

    return response()->json([
        'institution' => $institution->name,
        'academy' => config('sullam.academy_portal_url'),
        'data' => $programs,
        'privacy' => 'Endpoint ini hanya memuat katalog publik contoh dan tidak memuat progres atau data pribadi pengguna.',
    ]);
})->name('api.academy-preview');
