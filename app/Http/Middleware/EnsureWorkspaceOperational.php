<?php

namespace App\Http\Middleware;

/** @phase 4.4 Multi-tenant Institution Foundation */

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceOperational
{
    public function handle(Request $request, Closure $next): Response
    {
        $workspace = $request->attributes->get('workspace') ?? $request->user()?->institution;

        if (! $workspace || $workspace->status === 'active') {
            return $next($request);
        }

        $routeName = (string) ($request->route()?->getName() ?? '');
        $allowed = in_array($routeName, ['dashboard', 'logout', 'workspace.switch'], true)
            || str_starts_with($routeName, 'profile.');

        if ($allowed) {
            return $next($request);
        }

        return redirect()->route('dashboard')->with(
            'warning',
            'Fitur operasional tersedia setelah ruang lembaga berstatus aktif.',
        );
    }
}
