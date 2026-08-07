<?php

use Illuminate\Support\Str;

$sessionDomain = env('SESSION_DOMAIN');

// Ketika website, app, dan Academy berjalan sebagai subdomain dari domain yang
// sama, gunakan cookie session bersama secara otomatis. Nilai eksplisit
// SESSION_DOMAIN tetap selalu menang. Ini membuat login app.sullamulhifz.or.id
// dapat langsung digunakan di academy.sullamulhifz.or.id tanpa akun/session baru.
if (($sessionDomain === null || $sessionDomain === '')
    && filter_var(env('DOMAIN_SEPARATION_ENABLED', false), FILTER_VALIDATE_BOOL)) {
    $publicUrl = (string) env('PUBLIC_SITE_URL', env('APP_URL', ''));
    $publicHost = strtolower((string) parse_url($publicUrl, PHP_URL_HOST));
    $publicHost = preg_replace('/^www\./', '', $publicHost) ?: '';
    if ($publicHost !== '' && str_contains($publicHost, '.')) {
        $sessionDomain = '.'.$publicHost;
    }
}

return [
    'driver' => env('SESSION_DRIVER', 'file'),
    'lifetime' => (int) env('SESSION_LIFETIME', 120),
    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),
    'encrypt' => env('SESSION_ENCRYPT', false),
    'files' => storage_path('framework/sessions'),
    'connection' => env('SESSION_CONNECTION'),
    'table' => env('SESSION_TABLE', 'sessions'),
    'store' => env('SESSION_STORE'),
    'lottery' => [2, 100],
    'cookie' => env('SESSION_COOKIE', Str::slug(env('APP_NAME', 'laravel'), '_').'_session'),
    'path' => env('SESSION_PATH', '/'),
    'domain' => $sessionDomain,
    'secure' => env('SESSION_SECURE_COOKIE'),
    'http_only' => env('SESSION_HTTP_ONLY', true),
    'same_site' => env('SESSION_SAME_SITE', 'lax'),
    'partitioned' => env('SESSION_PARTITIONED_COOKIE', false),
];
