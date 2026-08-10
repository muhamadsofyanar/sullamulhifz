<?php

namespace App\Http\Middleware;

/** @phase 6.0 API domain isolation hardening */

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforceDomainSeparation
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('sullam.domain_separation_enabled')) {
            return $next($request);
        }

        $host = strtolower($request->getHost());
        $portalHost = $this->portalHost();
        $publicHost = $this->hostFromUrl(config('sullam.public_url'));
        $publicHosts = $this->publicHosts($publicHost);
        $academyHost = $this->academyHost();
        $apiHost = $this->apiHost();
        $stagingHost = $this->stagingHost();

        if ($apiHost && $host === $apiHost) {
            $path = trim($request->path(), '/');
            if ($path === '' || $path === 'up' || $path === 'api' || str_starts_with($path, 'api/')) {
                return $next($request);
            }

            return response()->json(['message' => 'Endpoint tidak ditemukan.'], 404);
        }

        $trimmedPath = trim($request->path(), '/');
        if ($apiHost && $host !== $apiHost && ($trimmedPath === 'api' || str_starts_with($trimmedPath, 'api/'))) {
            return response()->json(['message' => 'Endpoint tidak ditemukan.'], 404);
        }

        if ($stagingHost && $host === $stagingHost && ! config('sullam.staging_enabled')) {
            return response('Staging belum diaktifkan pada resource ini.', 404)
                ->header('X-Robots-Tag', 'noindex, nofollow, noarchive');
        }

        if (! $portalHost || ! $publicHost) {
            return $next($request);
        }

        // Academy v2.2 mempunyai portal mandiri pada subdomain academy. Route Academy
        // dan route autentikasi boleh berjalan langsung di host ini. Route operasional
        // lainnya tetap dikembalikan ke app.sullamulhifz.or.id agar pemisahan produk jelas.
        if ($academyHost && $host === $academyHost) {
            $routeName = (string) optional($request->route())->getName();
            $academyRoute = str_starts_with($routeName, 'academy.portal.');
            $authRoute = in_array($routeName, [
                'login', 'login.store', 'password.request', 'password.email',
                'password.reset', 'password.update', 'activation.show',
                'activation.store', 'logout',
            ], true);

            if ($academyRoute || $authRoute) {
                return $next($request);
            }

            if (str_starts_with($routeName, 'public.')) {
                return $this->redirectTo($this->publicBaseUrl(), $request->getPathInfo(), $request, 302);
            }

            if ($request->path() === 'academy' || $request->path() === 'academy/belajar') {
                return redirect()->away('https://'.$academyHost, 302);
            }

            if ($routeName !== '') {
                return $this->redirectTo(
                    $this->portalBaseUrl(),
                    $request->getPathInfo(),
                    $request,
                    $request->isMethodSafe() ? 302 : 307,
                );
            }

            return $next($request);
        }

        $routeName = (string) optional($request->route())->getName();
        $isPublicRoute = str_starts_with($routeName, 'public.');
        $isPortalRoute = ! $isPublicRoute;

        if ($host === $portalHost) {
            if ($request->path() === '/') {
                $target = auth()->check() ? '/dashboard' : '/login';

                return $this->redirectTo($this->portalBaseUrl(), $target, $request, 302);
            }

            if ($isPublicRoute) {
                return $this->redirectTo($this->publicBaseUrl(), $request->getPathInfo(), $request, 302);
            }

            return $next($request);
        }

        if (in_array($host, $publicHosts, true)) {
            if ($isPortalRoute) {
                return $this->redirectTo(
                    $this->portalBaseUrl(),
                    $request->getPathInfo(),
                    $request,
                    $request->isMethodSafe() ? 302 : 307,
                );
            }

            if ($host !== $publicHost) {
                return $this->redirectTo($this->publicBaseUrl(), $request->getPathInfo(), $request, 301);
            }

            return $next($request);
        }

        // Domain lama, localhost, dan healthcheck internal tetap berfungsi selama masa transisi.
        return $next($request);
    }

    private function redirectTo(string $baseUrl, string $path, Request $request, int $status): RedirectResponse
    {
        $path = '/'.ltrim($path, '/');
        $url = rtrim($baseUrl, '/').($path === '/' ? '' : $path);

        if ($request->getQueryString()) {
            $url .= '?'.$request->getQueryString();
        }

        return redirect()->away($url, $status);
    }


    private function apiHost(): ?string
    {
        $configured = trim((string) config('sullam.api_host'));

        return $configured !== '' ? strtolower($configured) : null;
    }

    private function stagingHost(): ?string
    {
        $configured = trim((string) config('sullam.staging_host'));

        return $configured !== '' ? strtolower($configured) : null;
    }

    private function academyHost(): ?string
    {
        $configured = trim((string) config('sullam.academy_host'));

        return $configured !== '' ? strtolower($configured) : null;
    }

    private function portalHost(): ?string
    {
        $configured = trim((string) config('sullam.portal_host'));

        return $configured !== ''
            ? strtolower($configured)
            : $this->hostFromUrl(config('sullam.portal_base_url'));
    }

    private function publicHosts(?string $canonicalHost): array
    {
        $hosts = array_map(
            static fn (mixed $host): string => strtolower(trim((string) $host)),
            (array) config('sullam.public_hosts', []),
        );

        if ($canonicalHost) {
            $hosts[] = $canonicalHost;
        }

        return array_values(array_unique(array_filter($hosts)));
    }

    private function publicBaseUrl(): string
    {
        return $this->origin((string) config('sullam.public_url'));
    }

    private function portalBaseUrl(): string
    {
        return $this->origin((string) config('sullam.portal_base_url'));
    }

    private function origin(string $url): string
    {
        $scheme = parse_url($url, PHP_URL_SCHEME) ?: 'https';
        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT);

        if (! $host) {
            return rtrim($url, '/');
        }

        return $scheme.'://'.$host.($port ? ':'.$port : '');
    }

    private function hostFromUrl(mixed $url): ?string
    {
        $host = parse_url((string) $url, PHP_URL_HOST);

        return $host ? strtolower($host) : null;
    }
}
