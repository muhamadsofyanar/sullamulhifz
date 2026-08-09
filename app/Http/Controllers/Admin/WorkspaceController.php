<?php

namespace App\Http\Controllers\Admin;

/** @phase 4.4 Multi-tenant Institution Foundation */

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Support\InstitutionType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function index(): View
    {
        return view('admin.workspaces.index', [
            'workspaces' => Institution::query()
                ->withCount('workspaceMemberships')
                ->latest()
                ->paginate(30),
            'institutionTypes' => InstitutionType::catalog(),
        ]);
    }

    public function status(Request $request, Institution $institution): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['active', 'rejected', 'suspended'])],
        ]);

        $institution->update([
            'status' => $data['status'],
            'onboarding_status' => $data['status'] === 'active' ? 'completed' : $data['status'],
            'approved_at' => $data['status'] === 'active' ? now() : null,
            'approved_by_user_id' => $data['status'] === 'active' ? $request->user()->id : null,
        ]);

        return back()->with('success', 'Status ruang '.$institution->name.' diperbarui.');
    }
}
