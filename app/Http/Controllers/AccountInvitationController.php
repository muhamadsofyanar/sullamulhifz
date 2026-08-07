<?php

namespace App\Http\Controllers;

use App\Models\AccountInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class AccountInvitationController extends Controller
{
    public function show(string $token): View
    {
        $invitation = $this->resolve($token);

        return view('auth.activate-account', compact('invitation', 'token'));
    }

    public function activate(Request $request, string $token): RedirectResponse
    {
        $invitation = $this->resolve($token);
        $data = $request->validate([
            'password' => ['required', 'confirmed', PasswordRule::min(12)->letters()->mixedCase()->numbers()],
            'accept_terms' => ['accepted'],
        ]);

        $user = DB::transaction(function () use ($invitation, $data) {
            $user = $invitation->user()->lockForUpdate()->firstOrFail();
            $user->update([
                'password' => Hash::make($data['password']),
                'status' => 'active',
                'must_change_password' => false,
                'email_verified_at' => $user->email ? ($user->email_verified_at ?: now()) : null,
                'phone_verified_at' => $user->phone ? ($user->phone_verified_at ?: now()) : null,
            ]);
            $invitation->update(['accepted_at' => now()]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Akun berhasil diaktifkan. Selamat datang di Sullamul Ḥifẓ.');
    }

    private function resolve(string $token): AccountInvitation
    {
        $invitation = AccountInvitation::with('user')
            ->where('token_hash', hash('sha256', $token))
            ->firstOrFail();

        abort_unless($invitation->isValid() && $invitation->user?->status !== 'inactive', 410, 'Tautan aktivasi sudah tidak berlaku.');

        return $invitation;
    }
}
