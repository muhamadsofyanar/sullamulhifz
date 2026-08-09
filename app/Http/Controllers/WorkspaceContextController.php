<?php

namespace App\Http\Controllers;

/** @phase 4.3 Identity & Relationship Core */

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WorkspaceContextController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        $data = $request->validate(['workspace_id' => ['required', 'integer']]);
        $workspaceId = (int) $data['workspace_id'];

        abort_unless($request->user()->isActiveMemberOf($workspaceId), 403, 'Anda tidak memiliki akses ke ruang ini.');

        $request->session()->put('workspace_id', $workspaceId);
        $request->session()->regenerateToken();

        return redirect()->route('dashboard')->with('success', 'Ruang kerja aktif berhasil diganti.');
    }
}
