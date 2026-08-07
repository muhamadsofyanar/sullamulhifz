<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->must_change_password) {
            return $next($request);
        }

        if ($request->routeIs(
            'profile.edit',
            'profile.password',
            'academy.portal.profile',
            'academy.portal.profile.password',
            'logout'
        )) {
            return $next($request);
        }

        $academyHost = strtolower(trim((string) config('sullam.academy_host')));
        $onAcademy = $academyHost !== '' && strtolower($request->getHost()) === $academyHost;

        return redirect()->route($onAcademy ? 'academy.portal.profile' : 'profile.edit')
            ->with('error', 'Ubah kata sandi awal sebelum menggunakan aplikasi.');
    }
}
