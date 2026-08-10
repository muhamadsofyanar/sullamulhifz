<?php

namespace App\Http\Controllers;

/** @phase 5.3 Mobile, Offline & Global */

use App\Models\UserPreference;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PreferenceController extends Controller
{
    public function edit(Request $request): View
    {
        $preference = UserPreference::firstOrCreate(
            ['user_id' => $request->user()->id],
            ['locale' => 'id', 'timezone' => $request->user()->institution?->timezone ?: 'Asia/Jakarta', 'pwa_enabled' => true],
        );

        return view('profile.preferences', [
            'preference' => $preference,
            'locales' => ['id' => 'Bahasa Indonesia', 'en' => 'English', 'ar' => 'العربية'],
            'timezones' => ['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura', 'Asia/Kuala_Lumpur', 'Asia/Singapore', 'Asia/Riyadh', 'UTC'],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'locale' => ['required', Rule::in(['id', 'en', 'ar'])],
            'timezone' => ['required', Rule::in(['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura', 'Asia/Kuala_Lumpur', 'Asia/Singapore', 'Asia/Riyadh', 'UTC'])],
            'pwa_enabled' => ['nullable', 'boolean'],
            'email_notifications' => ['nullable', 'boolean'],
            'whatsapp_notifications' => ['nullable', 'boolean'],
        ]);

        UserPreference::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'locale' => $data['locale'],
                'timezone' => $data['timezone'],
                'pwa_enabled' => (bool) ($data['pwa_enabled'] ?? false),
                'notification_preferences' => [
                    'email' => (bool) ($data['email_notifications'] ?? false),
                    'whatsapp' => (bool) ($data['whatsapp_notifications'] ?? false),
                ],
            ],
        );

        return back()->with('success', 'Preferensi perangkat, bahasa, dan zona waktu disimpan.');
    }
}
