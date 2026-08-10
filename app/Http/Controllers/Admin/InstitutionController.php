<?php

namespace App\Http\Controllers\Admin;

/** @phase 6.0 Public Academy opt-in */

/** @phase 4.4 Multi-tenant Institution Foundation */

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Support\InstitutionType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstitutionController extends Controller
{
    public function edit(Request $request): View
    {
        $institution = $request->user()->institution;
        abort_unless($institution, 404);

        return view('admin.institution.edit', [
            'institution' => $institution,
            'activeYear' => AcademicYear::where('institution_id', $institution->id)->where('is_active', true)->first(),
            'institutionTypes' => InstitutionType::catalog(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $institution = $request->user()->institution;
        abort_unless($institution, 404);

        $data = $request->validate([
            'name' => ['required','string','max:190'],
            'legal_name' => ['nullable','string','max:190'],
            'phone' => ['nullable','string','max:50'],
            'email' => ['nullable','email','max:190'],
            'address' => ['nullable','string','max:2000'],
            'institution_type' => ['required', \Illuminate\Validation\Rule::in(InstitutionType::keys())],
            'brand_primary_color' => ['required','regex:/^#[0-9a-fA-F]{6}$/'],
            'brand_secondary_color' => ['required','regex:/^#[0-9a-fA-F]{6}$/'],
            'term_student' => ['required','string','max:60'],
            'term_teacher' => ['required','string','max:60'],
            'term_guardian' => ['required','string','max:60'],
            'master_brand' => ['required','string','max:190'],
            'tagline' => ['required','string','max:190'],
            'brand_relation' => ['nullable','string','max:255'],
            'leader_name' => ['nullable','string','max:190'],
            'vision' => ['nullable','string','max:3000'],
            'mission' => ['nullable','string','max:5000'],
            'report_footer' => ['nullable','string','max:500'],
            'registration_notes' => ['nullable','string','max:3000'],
            'public_academy' => ['nullable','boolean'],
        ]);

        $institution->update([
            'name' => $data['name'],
            'legal_name' => $data['legal_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'institution_type' => $data['institution_type'],
            'brand_primary_color' => strtolower($data['brand_primary_color']),
            'brand_secondary_color' => strtolower($data['brand_secondary_color']),
            'terminology' => [
                'student' => $data['term_student'],
                'teacher' => $data['term_teacher'],
                'guardian' => $data['term_guardian'],
            ],
        ]);

        $settings = $institution->settings ?: [];
        $profile = [
            'master_brand' => $data['master_brand'],
            'tagline' => $data['tagline'],
            'brand_relation' => $data['brand_relation'] ?? null,
            'leader_name' => $data['leader_name'] ?? null,
            'vision' => $data['vision'] ?? null,
            'mission' => $data['mission'] ?? null,
            'report_footer' => $data['report_footer'] ?? null,
            'registration_notes' => $data['registration_notes'] ?? null,
            'public_academy' => (bool) ($data['public_academy'] ?? false),
        ];
        $profile['profile_completed'] = collect([
            $institution->address,
            $institution->phone,
            $profile['leader_name'],
            $profile['vision'],
            $profile['mission'],
        ])->every(fn ($value) => filled($value));

        $institution->update(['settings' => array_replace($settings, $profile)]);

        return back()->with('success', 'Profil lembaga berhasil disimpan. Data yang belum tersedia boleh tetap kosong.');
    }
}
