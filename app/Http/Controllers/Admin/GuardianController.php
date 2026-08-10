<?php

namespace App\Http\Controllers\Admin;

/** @phase 6.0 Workspace-scoped guardian status hardening */

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class GuardianController extends Controller
{
    public function index(Request $request): View
    {
        $guardians = Guardian::with(['user','students.currentEnrollment.schoolClass'])
            ->where('institution_id', $request->user()->institution_id)
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = '%'.$request->string('q').'%';
                $query->where(function ($q) use ($search): void {
                    $q->where('full_name', 'like', $search)
                        ->orWhere('phone', 'like', $search)
                        ->orWhere('email', 'like', $search);
                });
            })
            ->orderBy('full_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.guardians.index', compact('guardians'));
    }

    public function show(Request $request, Guardian $guardian): View
    {
        $this->guardInstitution($request, $guardian);
        $guardian->load(['user.roles','students.currentEnrollment.schoolClass']);
        return view('admin.guardians.show', compact('guardian'));
    }

    public function update(Request $request, Guardian $guardian): RedirectResponse
    {
        $this->guardInstitution($request, $guardian);
        $data = $request->validate([
            'full_name' => ['required','string','max:190'],
            'phone' => ['nullable','string','max:30', Rule::unique('users','phone')->ignore($guardian->user_id)],
            'email' => ['nullable','email','max:190', Rule::unique('users','email')->ignore($guardian->user_id)],
            'occupation' => ['nullable','string','max:100'],
            'address' => ['nullable','string'],
            'status' => ['required', Rule::in(['active','inactive'])],
        ]);

        $guardian->update($data);
        if ($guardian->user) {
            $guardian->user->update([
                'name' => $data['full_name'],
                'phone' => $data['phone'],
                'email' => $data['email'],
            ]);
            DB::table('workspace_memberships')
                ->where('institution_id', $guardian->institution_id)
                ->where('user_id', $guardian->user_id)
                ->update([
                    'status' => $data['status'],
                    'left_at' => $data['status'] === 'inactive' ? now() : null,
                    'updated_at' => now(),
                ]);
            DB::table('user_roles')
                ->where('institution_id', $guardian->institution_id)
                ->where('user_id', $guardian->user_id)
                ->whereIn('role_id', DB::table('roles')->where('name', 'guardian')->select('id'))
                ->update(['status' => $data['status'], 'updated_at' => now()]);
        }

        return back()->with('success', 'Profil wali berhasil diperbarui.');
    }

    private function guardInstitution(Request $request, Guardian $guardian): void
    {
        abort_unless($guardian->institution_id === $request->user()->institution_id, 404);
    }
}
