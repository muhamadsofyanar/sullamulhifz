<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->institution_id === $request->user()->institution_id, 404);

        $data = $request->validate([
            'password' => ['required', 'confirmed', 'min:10'],
        ]);

        $user->update([
            'password' => Hash::make($data['password']),
            'must_change_password' => true,
        ]);

        return back()->with('success', 'Kata sandi awal berhasil diatur ulang. Pengguna wajib menggantinya saat login.');
    }
}
