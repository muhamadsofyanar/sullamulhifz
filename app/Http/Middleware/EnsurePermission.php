<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();
        abort_unless($user, 401);

        $permissions = collect($permissions)
            ->flatMap(fn (string $item) => explode(',', $item))
            ->map(fn (string $item) => trim($item))
            ->filter()
            ->values()
            ->all();

        abort_unless($permissions !== [] && $user->hasAnyPermission($permissions), 403, 'Anda tidak memiliki izin untuk tindakan ini.');

        return $next($request);
    }
}
