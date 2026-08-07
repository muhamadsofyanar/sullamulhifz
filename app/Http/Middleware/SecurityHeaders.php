<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(self), microphone=(self), geolocation=(), payment=()');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin-allow-popups');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');

        if ($request->user() || $request->is('media/*')) {
            $response->headers->set('Cache-Control', 'private, no-store, max-age=0, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
        }

        if (strtolower($request->getHost()) === strtolower((string) config('sullam.staging_host'))) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
        }

        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}
