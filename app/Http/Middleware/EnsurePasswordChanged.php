<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->must_change_password
            && ! $request->routeIs('profile.edit', 'profile.password', 'logout')) {
            return redirect()->route('profile.edit')
                ->with('error', 'Ubah kata sandi awal sebelum menggunakan aplikasi.');
        }

        return $next($request);
    }
}
