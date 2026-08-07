<?php

namespace App\Http\Middleware;

use App\Support\Feature;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        $institutionId = $request->user()?->institution_id;
        abort_unless(Feature::enabled($featureKey, $institutionId), 404, 'Modul belum diaktifkan untuk lembaga ini.');

        return $next($request);
    }
}
