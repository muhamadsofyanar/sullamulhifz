<?php

namespace App\Http\Controllers;

/** @phase 5.0 Business defaults; @phase 5.2 Smart Assistant defaults; @phase 5.3 Global preferences defaults */

/** @phase 4.4 Multi-tenant Institution Foundation */

use App\Models\Branch;
use App\Models\FeatureFlag;
use App\Models\Institution;
use App\Models\Role;
use App\Models\User;
use App\Models\WorkspaceMembership;
use App\Support\InstitutionType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class InstitutionRegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register-institution', ['institutionTypes' => InstitutionType::catalog()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'institution_name' => ['required', 'string', 'max:190'],
            'institution_type' => ['required', Rule::in(InstitutionType::keys())],
            'admin_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
            'terms' => ['accepted'],
        ]);

        [$user, $institution] = DB::transaction(function () use ($data): array {
            $token = Str::lower(str_replace('-', '', (string) Str::uuid()));
            $terminology = InstitutionType::terminology($data['institution_type']);
            $institution = Institution::create([
                'name' => $data['institution_name'],
                'code' => 'ORG-'.strtoupper(substr($token, 0, 12)),
                'slug' => Str::slug($data['institution_name']).'-'.substr($token, 0, 8),
                'workspace_type' => 'institution',
                'institution_type' => $data['institution_type'],
                'privacy_mode' => 'institution',
                'onboarding_status' => 'pending_review',
                'registration_source' => 'public_institution_registration',
                'terminology' => [
                    'student' => $terminology['student'],
                    'teacher' => $terminology['teacher'],
                    'guardian' => $terminology['guardian'],
                ],
                'timezone' => config('app.timezone', 'Asia/Jakarta'),
                'status' => 'onboarding',
                'settings' => [
                    'master_brand' => 'Sullamul Ḥifẓ',
                    'tagline' => 'Bukan Sekadar Hafal, Tapi KUAT',
                    'created_via' => 'public_institution_registration',
                ],
            ]);

            $user = User::create([
                'institution_id' => $institution->id,
                'name' => $data['admin_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?: null,
                'password' => $data['password'],
                'status' => 'active',
            ]);

            $role = Role::query()->where('name', 'institution_admin')->firstOrFail();
            $user->roles()->attach($role->id, [
                'institution_id' => $institution->id,
                'status' => 'active',
            ]);

            $branch = Branch::create([
                'institution_id' => $institution->id,
                'name' => 'Unit Utama',
                'code' => 'UTAMA',
                'status' => 'active',
                'is_main' => true,
            ]);

            WorkspaceMembership::create([
                'institution_id' => $institution->id,
                'user_id' => $user->id,
                'role_id' => $role->id,
                'branch_id' => $branch->id,
                'membership_type' => 'owner',
                'display_label' => 'Pengelola '.InstitutionType::label($data['institution_type']),
                'status' => 'active',
                'is_default' => true,
                'joined_at' => now(),
            ]);

            foreach (['core_academic', 'public_website', 'quran_audio', 'quran_journey', 'report_cards', 'api_integrations', 'business_center', 'smart_assistant', 'user_preferences'] as $featureKey) {
                FeatureFlag::firstOrCreate(
                    ['institution_id' => $institution->id, 'feature_key' => $featureKey],
                    ['enabled' => true],
                );
            }

            $institution->update(['owner_user_id' => $user->id]);

            return [$user, $institution];
        });

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('workspace_id', $institution->id);

        return redirect()->route('dashboard')->with('success', 'Ruang lembaga dibuat dan menunggu pemeriksaan platform.');
    }
}
