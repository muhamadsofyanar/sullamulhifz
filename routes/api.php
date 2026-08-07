<?php

use Illuminate\Support\Facades\Route;

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
    'documentation_status' => 'starter',
]))->name('api.meta');
