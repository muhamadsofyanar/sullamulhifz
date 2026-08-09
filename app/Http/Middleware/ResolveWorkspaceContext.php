<?php

namespace App\Http\Middleware;

/** @phase 4.3 Identity & Relationship Core */

use App\Models\Institution;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ResolveWorkspaceContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! Schema::hasTable('workspace_memberships')) {
            return $next($request);
        }

        $memberships = $user->workspaceMemberships()
            ->active()
            ->with('institution')
            ->whereHas('institution', fn ($query) => $query->whereIn('status', ['active', 'onboarding']))
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get()
            ->unique('institution_id')
            ->values();

        $requestedId = (int) $request->session()->get('workspace_id', 0);
        $membership = $memberships->firstWhere('institution_id', $requestedId)
            ?? $memberships->firstWhere('institution_id', (int) $user->getRawOriginal('institution_id'))
            ?? $memberships->first();

        if ($membership?->institution) {
            $workspace = $membership->institution;
            $request->session()->put('workspace_id', $workspace->id);
            $user->setAttribute('institution_id', $workspace->id);
            $user->setRelation('institution', $workspace);
            $request->attributes->set('workspace', $workspace);
        } else {
            $request->session()->forget('workspace_id');
            $workspace = $user->institution;
        }

        View::share([
            'activeWorkspace' => $workspace,
            'workspaceOptions' => $memberships,
        ]);

        return $next($request);
    }
}
