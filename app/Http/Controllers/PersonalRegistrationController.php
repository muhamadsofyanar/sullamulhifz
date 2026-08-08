<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\PersonalProfile;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PersonalRegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register-personal');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
            'terms' => ['accepted'],
        ]);

        $user = DB::transaction(function () use ($data): User {
            $token = Str::lower(str_replace('-', '', (string) Str::uuid()));
            $institution = Institution::create([
                'name' => 'Ruang Personal '.Str::limit($data['name'], 70, ''),
                'code' => 'PRS-'.strtoupper(substr($token, 0, 12)),
                'slug' => 'personal-'.substr($token, 0, 20),
                'workspace_type' => 'personal',
                'privacy_mode' => 'private',
                'timezone' => config('app.timezone', 'Asia/Jakarta'),
                'status' => 'active',
                'settings' => ['private' => true, 'created_via' => 'public_personal_registration'],
            ]);

            $user = User::create([
                'institution_id' => $institution->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?: null,
                'password' => $data['password'],
                'status' => 'active',
            ]);

            $role = Role::query()->where('name', 'personal')->firstOrFail();
            $user->roles()->attach($role->id, [
                'institution_id' => $institution->id,
                'status' => 'active',
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
                'privacy_acknowledged_at' => now(),
            ]);

            $institution->update(['owner_user_id' => $user->id]);
            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('personal.dashboard')->with('success', 'Akun Personal siap. Sekarang atur ritme perjalanan Anda.');
    }
}
