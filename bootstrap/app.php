<?php

use App\Console\Commands\SyncQuranAudio;
use App\Console\Commands\PurgeExpiredMedia;
use App\Console\Commands\SecureLegacyMedia;
use App\Http\Middleware\EnforceDomainSeparation;
use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\EnsurePermission;
use App\Http\Middleware\EnsureFeatureEnabled;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

/*
 * IMPORTANT:
 * Middleware configuration is registered while the Application is still being
 * constructed. At this point Laravel's config repository is not guaranteed to
 * be available yet (notably during Composer's `artisan package:discover`).
 * Therefore trusted proxies must be read from the process environment directly
 * instead of calling config() here.
 */
$trustedProxyCsv = getenv('TRUSTED_PROXIES');
if ($trustedProxyCsv === false || trim($trustedProxyCsv) === '') {
    $trustedProxyCsv = '127.0.0.1,10.0.0.0/8,172.16.0.0/12,192.168.0.0/16,100.64.0.0/10';
}

$trustedProxies = array_values(array_filter(array_map(
    static fn (string $proxy): string => trim($proxy),
    explode(',', $trustedProxyCsv),
), static fn (string $proxy): bool => $proxy !== ''));

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([SyncQuranAudio::class, PurgeExpiredMedia::class, SecureLegacyMedia::class])
    ->withMiddleware(function (Middleware $middleware) use ($trustedProxies): void {
        $middleware->trustProxies(at: $trustedProxies);
        $middleware->web(append: [EnforceDomainSeparation::class, SecurityHeaders::class]);
        $middleware->alias([
            'role' => EnsureRole::class,
            'password.changed' => EnsurePasswordChanged::class,
            'permission' => EnsurePermission::class,
            'feature' => EnsureFeatureEnabled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Keep the default Laravel exception rendering.
    })->create();
