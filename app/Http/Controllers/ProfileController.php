<?php

namespace App\Http\Controllers;

use App\Models\LoginHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View { return view('profile', ['user'=>$request->user()->load('roles'),'loginHistories'=>LoginHistory::where('user_id',$request->user()->id)->latest('logged_in_at')->limit(15)->get()]); }
    public function updatePassword(Request $request): RedirectResponse
    {
        $data=$request->validate(['current_password'=>['required','current_password'],'password'=>['required','confirmed', Password::min(12)->letters()->mixedCase()->numbers()]]);
        $request->user()->update(['password'=>Hash::make($data['password']),'must_change_password'=>false,'remember_token'=>null]);
        return back()->with('success','Kata sandi berhasil diperbarui.');
    }
}
