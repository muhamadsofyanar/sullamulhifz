<?php

/** @phase 5.1 SaaS Production Readiness; @phase 5.3 Mobile & Global API metadata; @phase 6.0 Public Academy opt-in */

use App\Models\AcademyProgram;
use App\Http\Controllers\CommunicationWebhookController;
use App\Models\Institution;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::post('/v1/webhooks/communications/whatsapp/{connection}', [CommunicationWebhookController::class, 'whatsapp'])
    ->middleware('throttle:120,1')
    ->name('api.webhooks.communications.whatsapp');

Route::get('/health', function () {
    $database = true;
    try {
        DB::select('select 1');
    } catch (\Throwable $exception) {
        $database = false;
    }
    $storage = is_writable(storage_path()) && is_writable(storage_path('framework'));

    return response()->json([
        'status' => ($database && $storage) ? 'ok' : 'degraded',
        'product' => 'Sullamul Hifz',
        'release' => trim((string) @file_get_contents(base_path('RELEASE'))) ?: 'unknown',
        'checks' => ['database' => $database ? 'ok' : 'error', 'storage' => $storage ? 'ok' : 'error'],
        'time' => now()->toIso8601String(),
    ], ($database && $storage) ? 200 : 503);
})->name('api.health');

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
    'capabilities' => [
        'pwa' => true,
        'offline_safe_shell' => true,
        'locales' => ['id', 'en', 'ar'],
        'timezones' => ['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura', 'Asia/Kuala_Lumpur', 'Asia/Singapore', 'Asia/Riyadh', 'UTC'],
        'private_data_offline_cache' => false,
    ],
    'documentation_status' => 'starter',
]))->name('api.meta');

Route::get('/v1/academy-preview', function () {
    $institution = Institution::query()->where('status', 'active')->orderBy('id')->get()
        ->first(fn (Institution $item): bool => (bool) $item->setting('public_academy', false));
    if (! $institution) {
        return response()->json(['data' => [], 'note' => 'Belum ada lembaga yang mempublikasikan katalog Academy.']);
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
