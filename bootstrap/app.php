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

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([SyncQuranAudio::class, PurgeExpiredMedia::class, SecureLegacyMedia::class])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: config('sullam.trusted_proxies', ['127.0.0.1']));
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
