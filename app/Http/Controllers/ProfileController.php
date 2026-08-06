<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View { return view('profile', ['user'=>$request->user()]); }
    public function updatePassword(Request $request): RedirectResponse
    {
        $data=$request->validate(['current_password'=>['required','current_password'],'password'=>['required','confirmed','min:10']]);
        $request->user()->update(['password'=>Hash::make($data['password']),'must_change_password'=>false]);
        return back()->with('success','Kata sandi berhasil diperbarui.');
    }
}
