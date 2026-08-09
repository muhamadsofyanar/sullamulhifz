<?php

namespace App\Http\Middleware;

use App\Services\PersonalModuleAccessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePersonalModuleEnrollment
{
    public function __construct(private readonly PersonalModuleAccessService $access) {}

    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        $user = $request->user();
        if ($user && $user->hasRole('personal')) {
            abort_unless(
                $this->access->allows($user, $moduleKey),
                403,
                'Program ini belum terdaftar di Ruang Personal Anda. Buka Program Saya untuk mengaktifkannya.',
            );
        }

        return $next($request);
    }
}
