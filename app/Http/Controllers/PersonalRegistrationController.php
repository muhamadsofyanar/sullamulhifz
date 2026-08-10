<?php

namespace App\Http\Controllers;

/** @phase 5.0 Business defaults; @phase 5.2 Smart Assistant defaults; @phase 5.3 Global preferences defaults */

/** @phase 4.3 Identity & Relationship Core; @phase 4.4 Multi-tenant metadata; @phase 4.5 Personal 2.0 */

use App\Models\Institution;
use App\Models\FeatureFlag;
use App\Models\PersonalProfile;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use App\Models\WorkspaceMembership;
use App\Services\PersonalModuleAccessService;
use App\Support\PersonalIdentity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PersonalRegistrationController extends Controller
{
    public function __construct(private readonly PersonalModuleAccessService $personalModules) {}

    public function create(): View
    {
        return view('auth.register-personal', [
            'programs' => $this->personalModules->registrationCatalog(),
            'ageGroups' => PersonalIdentity::ageGroups(),
            'interestOptions' => PersonalIdentity::interests(),
            'learningModes' => PersonalIdentity::learningModes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $isMinor = PersonalIdentity::isMinor($request->input('age_group'));
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
            'programs' => ['sometimes', 'array', 'max:3'],
            'programs.*' => ['string', 'distinct', Rule::in($this->personalModules->registrationCatalog()->pluck('key')->all())],
            'age_group' => ['nullable', Rule::in(array_keys(PersonalIdentity::ageGroups()))],
            'interests' => ['sometimes', 'array', 'max:5'],
            'interests.*' => ['string', 'distinct', Rule::in(array_keys(PersonalIdentity::interests()))],
            'aspiration' => ['nullable', 'string', 'max:150'],
            'learning_mode' => ['nullable', Rule::in(array_keys(PersonalIdentity::learningModes()))],
            'guardian_acknowledgement' => $isMinor ? ['required', 'accepted'] : ['nullable'],
            'terms' => ['accepted'],
        ]);

        $selectedPrograms = collect($data['programs'] ?? [])->unique()->values();
        $safeguardingAcknowledgedAt = $isMinor
            && $request->boolean('guardian_acknowledgement') ? now() : null;

        $user = DB::transaction(function () use ($data, $selectedPrograms, $safeguardingAcknowledgedAt): User {
            $token = Str::lower(str_replace('-', '', (string) Str::uuid()));
            $institution = Institution::create([
                'name' => 'Ruang Personal '.Str::limit($data['name'], 70, ''),
                'code' => 'PRS-'.strtoupper(substr($token, 0, 12)),
                'slug' => 'personal-'.substr($token, 0, 20),
                'workspace_type' => 'personal',
                'institution_type' => 'personal',
                'privacy_mode' => 'private',
                'onboarding_status' => 'completed',
                'registration_source' => 'public_personal_registration',
                'timezone' => config('app.timezone', 'Asia/Jakarta'),
                'status' => 'active',
                'settings' => ['private' => true, 'created_via' => 'public_personal_registration'],
            ]);

            $user = User::create([
                'institution_id' => $institution->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'status' => 'active',
            ]);

            $role = Role::query()->where('name', 'personal')->firstOrFail();
            $user->roles()->attach($role->id, [
                'institution_id' => $institution->id,
                'status' => 'active',
            ]);

            WorkspaceMembership::create([
                'institution_id' => $institution->id,
                'user_id' => $user->id,
                'role_id' => $role->id,
                'membership_type' => 'learner',
                'display_label' => 'Ruang Personal',
                'status' => 'active',
                'is_default' => true,
                'joined_at' => now(),
            ]);

            $student = Student::create([
                'institution_id' => $institution->id,
                'student_code' => 'PERSONAL-'.$user->id,
                'full_name' => $user->name,
                'joined_at' => today(),
                'status' => 'active',
                'stifin_status' => 'untested',
            ]);

            PersonalProfile::create([
                'institution_id' => $institution->id,
                'user_id' => $user->id,
                'student_id' => $student->id,
                'daily_minutes' => 20,
                'age_group' => $data['age_group'] ?? null,
                'interests' => array_values($data['interests'] ?? []),
                'aspiration' => $data['aspiration'] ?? null,
                'learning_mode' => $data['learning_mode'] ?? 'self',
                'safeguarding_acknowledged_at' => $safeguardingAcknowledgedAt,
                'privacy_acknowledged_at' => now(),
            ]);

            foreach (['academy_portal', 'quran_audio', 'quran_journey', 'business_center', 'smart_assistant', 'user_preferences'] as $featureKey) {
                FeatureFlag::firstOrCreate(
                    ['institution_id' => $institution->id, 'feature_key' => $featureKey],
                    ['enabled' => true],
                );
            }

            foreach ($selectedPrograms as $moduleKey) {
                $this->personalModules->enroll($user, (string) $moduleKey, 'public_registration');
            }

            $institution->update(['owner_user_id' => $user->id]);
            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('workspace_id', $user->institution_id);

        return redirect()->route('personal.dashboard')->with('success', 'Akun Personal siap. Sekarang atur ritme perjalanan Anda.');
    }
}
