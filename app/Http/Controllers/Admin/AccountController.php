<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountInvitation;
use App\Models\ActivityLog;
use App\Models\User;
use App\Notifications\AccountInvitationNotification;
use App\Services\Communication\CommunicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AccountController extends Controller
{
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorizeInstitution($request, $user);

        $data = $request->validate([
            'password' => ['required', 'confirmed', PasswordRule::min(12)->letters()->mixedCase()->numbers()],
        ]);

        $user->update([
            'password' => Hash::make($data['password']),
            'must_change_password' => true,
        ]);

        $this->log($request, $user, 'password_reset_by_admin');

        return back()->with('success', 'Kata sandi awal berhasil diatur ulang. Pengguna wajib menggantinya saat login.');
    }

    public function invite(Request $request, User $user): RedirectResponse
    {
        $this->authorizeInstitution($request, $user);

        AccountInvitation::query()
            ->where('user_id', $user->id)
            ->whereNull('accepted_at')
            ->delete();

        $plainToken = Str::random(64);
        $invitation = AccountInvitation::create([
            'institution_id' => $user->institution_id,
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plainToken),
            'delivery_channel' => $user->email ? 'email' : 'manual',
            'delivery_address' => $user->email ?: $user->phone,
            'expires_at' => now()->addHours(48),
            'created_by_user_id' => $request->user()->id,
        ]);

        $activationUrl = rtrim((string) config('sullam.portal_base_url'), '/').'/aktivasi/'.$plainToken;

        $delivery = null;
        try {
            $delivery = app(CommunicationService::class)->sendAccountInvitation($user, $activationUrl, $request->user()->id);
        } catch (\Throwable $exception) {
            report($exception);
        }

        if (! $delivery && $user->email) {
            try {
                $user->notify(new AccountInvitationNotification($activationUrl));
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        if ($delivery) {
            $invitation->update([
                'delivery_channel' => $delivery->channel,
                'delivery_address' => $delivery->recipient_address,
            ]);
        }

        $this->log($request, $user, 'account_invitation_created', ['invitation_id' => $invitation->id]);

        return back()
            ->with('success', $delivery
                ? 'Undangan aktivasi dibuat dan diproses melalui '.strtoupper($delivery->channel).'. Tautan juga tersedia di bawah.'
                : ($user->email
                    ? 'Undangan aktivasi dibuat melalui email bawaan. Pastikan konfigurasi email aktif; tautan juga tersedia di bawah.'
                    : 'Undangan aktivasi dibuat. Salin tautan berikut dan kirim secara pribadi kepada pengguna.'))
            ->with('activation_url', $activationUrl);
    }

    private function authorizeInstitution(Request $request, User $user): void
    {
        $actor = $request->user();
        abort_unless(
            ($actor->hasRole('superadmin') && $actor->institution_id === null) || (int) $user->institution_id === (int) $actor->institution_id,
            404,
        );
    }

    private function log(Request $request, User $subject, string $action, array $newValues = []): void
    {
        ActivityLog::create([
            'institution_id' => $subject->institution_id,
            'user_id' => $request->user()->id,
            'action' => $action,
            'subject_type' => User::class,
            'subject_id' => $subject->id,
            'new_values' => $newValues ?: null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
