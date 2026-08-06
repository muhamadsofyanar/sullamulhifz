<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:190'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $field = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';
        $credentials = [$field => $data['login'], 'password' => $data['password'], 'status' => 'active'];

        if (! Auth::attempt($credentials, (bool) ($data['remember'] ?? false))) {
            return back()->withErrors(['login' => 'Email/nomor telepon atau kata sandi belum sesuai.'])->onlyInput('login');
        }

        $request->session()->regenerate();
        $request->user()->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah keluar dari aplikasi.');
    }
}
