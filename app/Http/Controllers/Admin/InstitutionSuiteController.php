<?php

namespace App\Http\Controllers\Admin;

/** @phase 4.7 Institution Suite */

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Guardian;
use App\Models\Institution;
use App\Models\Role;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMembership;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InstitutionSuiteController extends Controller
{
    private const INVITABLE_ROLES = [
        'institution_admin' => ['label' => 'Admin Lembaga', 'membership' => 'manager'],
        'head' => ['label' => 'Kepala/Pimpinan', 'membership' => 'manager'],
        'teacher' => ['label' => 'Guru/Ustadz', 'membership' => 'teacher'],
        'guardian' => ['label' => 'Orang Tua/Wali', 'membership' => 'guardian'],
    ];

    public function index(Request $request): View
    {
        $institution = $this->institution($request);
        $members = WorkspaceMembership::query()
            ->where('institution_id', $institution->id)
            ->with(['user', 'role'])
            ->orderByRaw("CASE status WHEN 'active' THEN 0 ELSE 1 END")
            ->latest('joined_at')
            ->paginate(30);
        $invitations = WorkspaceInvitation::query()
            ->where('institution_id', $institution->id)
            ->with(['role', 'invitedBy'])
            ->latest()
            ->limit(20)
            ->get();

        $checklist = $this->checklist($institution);
        $completed = collect($checklist)->where('complete', true)->count();

        return view('admin.institution-suite.index', [
            'institution' => $institution,
            'members' => $members,
            'invitations' => $invitations,
            'invitableRoles' => self::INVITABLE_ROLES,
            'checklist' => $checklist,
            'readinessPercent' => (int) round(($completed / max(1, count($checklist))) * 100),
            'canManage' => $request->user()->hasAnyRole(['superadmin', 'institution_admin']),
        ]);
    }

    public function invite(Request $request): RedirectResponse
    {
        $this->authorizeManager($request->user());
        $institution = $this->institution($request);
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:190'],
            'role' => ['required', Rule::in(array_keys(self::INVITABLE_ROLES))],
        ]);
        $email = strtolower($data['email']);
        $target = User::query()->where('email', $email)->where('status', 'active')->first();
        if (! $target) {
            throw ValidationException::withMessages([
                'email' => 'Akun aktif dengan email tersebut belum ada. Minta pengguna mendaftar terlebih dahulu, lalu kirim undangan kembali.',
            ]);
        }
        if ($target->isActiveMemberOf($institution->id)) {
            throw ValidationException::withMessages(['email' => 'Akun tersebut sudah menjadi anggota aktif lembaga ini.']);
        }

        $role = Role::query()->where('name', $data['role'])->firstOrFail();
        $plainToken = Str::random(64);
        WorkspaceInvitation::query()
            ->where('institution_id', $institution->id)
            ->where('email', $email)
            ->where('status', 'pending')
            ->update(['status' => 'revoked']);
        WorkspaceInvitation::create([
            'institution_id' => $institution->id,
            'invited_by_user_id' => $request->user()->id,
            'role_id' => $role->id,
            'membership_type' => self::INVITABLE_ROLES[$data['role']]['membership'],
            'email' => $email,
            'token_hash' => hash('sha256', $plainToken),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        return back()
            ->with('success', 'Undangan ruang dibuat dan berlaku tujuh hari.')
            ->with('institution_invitation_url', route('institution-suite.invitations.accept', ['token' => $plainToken]));
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = $this->validInvitation($request, $token);

        DB::transaction(function () use ($request, $invitation): void {
            $membership = WorkspaceMembership::updateOrCreate(
                [
                    'institution_id' => $invitation->institution_id,
                    'user_id' => $request->user()->id,
                    'membership_type' => $invitation->membership_type,
                ],
                [
                    'role_id' => $invitation->role_id,
                    'status' => 'active',
                    'is_default' => false,
                    'joined_at' => now(),
                    'left_at' => null,
                ],
            );
            DB::table('user_roles')->updateOrInsert(
                [
                    'user_id' => $request->user()->id,
                    'role_id' => $invitation->role_id,
                    'institution_id' => $invitation->institution_id,
                ],
                ['status' => 'active', 'valid_from' => today(), 'valid_until' => null, 'updated_at' => now(), 'created_at' => now()],
            );

            if ($invitation->role?->name === 'teacher') {
                Teacher::updateOrCreate(
                    ['institution_id' => $invitation->institution_id, 'user_id' => $request->user()->id],
                    ['full_name' => $request->user()->name, 'email' => $request->user()->email, 'phone' => $request->user()->phone, 'status' => 'active'],
                );
            }
            if ($invitation->role?->name === 'guardian') {
                Guardian::updateOrCreate(
                    ['institution_id' => $invitation->institution_id, 'user_id' => $request->user()->id],
                    ['full_name' => $request->user()->name, 'email' => $request->user()->email, 'phone' => $request->user()->phone, 'status' => 'active'],
                );
            }

            $invitation->update([
                'accepted_by_user_id' => $request->user()->id,
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);
            $request->session()->put('workspace_id', $membership->institution_id);
        });

        return redirect()->route('dashboard')->with('success', 'Anda telah bergabung ke ruang '.$invitation->institution->name.'.');
    }

    public function showInvitation(Request $request, string $token): View
    {
        return view('auth.accept-workspace', [
            'invitation' => $this->validInvitation($request, $token),
            'token' => $token,
        ]);
    }

    public function revoke(Request $request, WorkspaceInvitation $invitation): RedirectResponse
    {
        $this->authorizeManager($request->user());
        $institution = $this->institution($request);
        abort_unless((int) $invitation->institution_id === (int) $institution->id, 403);
        abort_unless($invitation->status === 'pending', 422);
        $invitation->update(['status' => 'revoked']);

        return back()->with('success', 'Undangan ruang dibatalkan.');
    }

    public function updateMember(Request $request, WorkspaceMembership $membership): RedirectResponse
    {
        $this->authorizeManager($request->user());
        $institution = $this->institution($request);
        abort_unless((int) $membership->institution_id === (int) $institution->id, 403);
        abort_if($membership->membership_type === 'owner', 422, 'Pemilik utama ruang tidak dapat dinonaktifkan dari halaman ini.');
        abort_if((int) $membership->user_id === (int) $request->user()->id, 422, 'Anda tidak dapat menonaktifkan keanggotaan sendiri.');
        $data = $request->validate(['status' => ['required', Rule::in(['active', 'suspended'])]]);

        DB::transaction(function () use ($membership, $data): void {
            $membership->update([
                'status' => $data['status'],
                'left_at' => $data['status'] === 'suspended' ? now() : null,
                'joined_at' => $data['status'] === 'active' ? ($membership->joined_at ?? now()) : $membership->joined_at,
                'is_default' => $data['status'] === 'active' ? $membership->is_default : false,
            ]);
            if ($membership->role_id) {
                DB::table('user_roles')
                    ->where('user_id', $membership->user_id)
                    ->where('role_id', $membership->role_id)
                    ->where('institution_id', $membership->institution_id)
                    ->update(['status' => $data['status'] === 'active' ? 'active' : 'inactive', 'updated_at' => now()]);
            }
        });

        return back()->with('success', 'Status anggota pada lembaga ini diperbarui tanpa memengaruhi ruang lainnya.');
    }

    private function institution(Request $request): Institution
    {
        $institution = $request->attributes->get('workspace') ?? $request->user()->institution;
        abort_unless($institution && $institution->workspace_type === 'institution', 403, 'Suite Lembaga hanya tersedia pada ruang lembaga.');

        return $institution;
    }

    private function authorizeManager(User $user): void
    {
        abort_unless($user->hasAnyRole(['superadmin', 'institution_admin']), 403, 'Hanya Admin Lembaga yang dapat mengubah keanggotaan.');
    }

    private function checklist(Institution $institution): array
    {
        return [
            ['label' => 'Identitas dan jenis lembaga', 'complete' => filled($institution->name) && filled($institution->institution_type)],
            ['label' => 'Kontak resmi lembaga', 'complete' => filled($institution->email) || filled($institution->phone)],
            ['label' => 'Tahun ajaran aktif', 'complete' => AcademicYear::query()->where('institution_id', $institution->id)->where('is_active', true)->exists()],
            ['label' => 'Guru/Ustadz aktif', 'complete' => Teacher::query()->withoutGlobalScopes()->where('institution_id', $institution->id)->where('status', 'active')->exists()],
            ['label' => 'Peserta/Santri aktif', 'complete' => Student::query()->withoutGlobalScopes()->where('institution_id', $institution->id)->where('status', 'active')->exists()],
            ['label' => 'Orang tua/wali aktif', 'complete' => Guardian::query()->where('institution_id', $institution->id)->where('status', 'active')->exists()],
        ];
    }

    private function validInvitation(Request $request, string $token): WorkspaceInvitation
    {
        $invitation = WorkspaceInvitation::query()
            ->with(['institution', 'role', 'invitedBy'])
            ->where('token_hash', hash('sha256', $token))
            ->firstOrFail();
        abort_unless($invitation->status === 'pending' && $invitation->expires_at->isFuture(), 410, 'Undangan sudah tidak berlaku.');
        abort_unless(strtolower((string) $request->user()->email) === strtolower((string) $invitation->email), 403, 'Undangan ini ditujukan untuk akun lain.');
        abort_unless($invitation->institution?->status === 'active', 422, 'Ruang lembaga belum aktif.');

        return $invitation;
    }
}
