<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guardian;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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
            'phone' => ['nullable','string','max:30'],
            'email' => ['nullable','email','max:190'],
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
                'status' => $data['status'],
            ]);
        }

        return back()->with('success', 'Profil wali berhasil diperbarui.');
    }

    private function guardInstitution(Request $request, Guardian $guardian): void
    {
        abort_unless($guardian->institution_id === $request->user()->institution_id, 404);
    }
}
