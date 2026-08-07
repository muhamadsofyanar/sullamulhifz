<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
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
            if (Schema::hasTable('login_histories')) LoginHistory::create([
                'login_identifier' => $data['login'],
                'was_successful' => false,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'logged_in_at' => now(),
            ]);

            return back()->withErrors(['login' => 'Email/nomor telepon atau kata sandi belum sesuai.'])->onlyInput('login');
        }

        $request->session()->regenerate();
        $user = $request->user();
        $loginPayload = ['last_login_at'=>now(),'last_login_ip'=>$request->ip()];
        if (Schema::hasColumn('users','login_count')) $loginPayload['login_count']=((int)$user->login_count)+1;
        $user->forceFill($loginPayload)->save();

        if (Schema::hasTable('login_histories')) LoginHistory::create([
            'user_id' => $user->id,
            'institution_id' => $user->institution_id,
            'login_identifier' => $data['login'],
            'was_successful' => true,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'logged_in_at' => now(),
        ]);

        $academyHost = strtolower(trim((string) config('sullam.academy_host')));
        $fallback = $academyHost !== '' && strtolower($request->getHost()) === $academyHost
            ? route('academy.portal.index')
            : route('dashboard');

        return redirect()->intended($fallback);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $academyHost = strtolower(trim((string) config('sullam.academy_host')));
        if ($academyHost !== '' && strtolower($request->getHost()) === $academyHost) {
            return redirect()->away('https://'.$academyHost.'/login')->with('success', 'Anda telah keluar dari Academy.');
        }

        return redirect()->route('login')->with('success', 'Anda telah keluar dari aplikasi.');
    }
}
