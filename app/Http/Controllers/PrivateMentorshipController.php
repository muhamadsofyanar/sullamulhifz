<?php

namespace App\Http\Controllers;

/** @phase 4.6 Private Ustadz */

use App\Models\MentorshipSession;
use App\Models\PersonalCheckIn;
use App\Models\PersonalGoal;
use App\Models\PersonalPracticeEntry;
use App\Models\StudentPortfolio;
use App\Models\User;
use App\Models\UserRelationship;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PrivateMentorshipController extends Controller
{
    private const SCOPES = [
        'progress_summary' => 'Ringkasan progres',
        'goals' => 'Target aktif',
        'practice' => 'Ringkasan latihan Qur’an',
        'portfolio' => 'Judul portofolio pilihan',
    ];

    public function index(Request $request): View
    {
        $user = $request->user();
        $relationships = UserRelationship::query()
            ->where('relationship_type', 'mentor_learner')
            ->where(fn ($query) => $query->where('from_user_id', $user->id)->orWhere('to_user_id', $user->id))
            ->with(['fromUser.personalProfile', 'toUser.personalProfile', 'mentorshipSessions' => fn ($query) => $query->latest('scheduled_at')->latest('id')])
            ->latest()
            ->get();

        $connections = $relationships->map(function (UserRelationship $relationship) use ($user): array {
            $learner = $relationship->fromUser;
            $mentor = $relationship->toUser;
            $scopes = array_values(array_intersect(array_keys(self::SCOPES), $relationship->visibility_scope ?? []));

            return [
                'relationship' => $relationship,
                'learner' => $learner,
                'mentor' => $mentor,
                'counterpart' => (int) $user->id === (int) $learner->id ? $mentor : $learner,
                'is_learner' => (int) $user->id === (int) $learner->id,
                'is_mentor' => (int) $user->id === (int) $mentor->id,
                'scopes' => $scopes,
                'snapshot' => $relationship->status === 'accepted' && (int) $user->id === (int) $mentor->id
                    ? $this->learnerSnapshot($learner, $scopes)
                    : null,
            ];
        });

        return view('mentorship.index', [
            'connections' => $connections,
            'scopeOptions' => self::SCOPES,
            'canInviteMentor' => $user->hasRole('personal'),
            'canInviteLearner' => $user->hasRole('teacher'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:190'],
            'visibility_scope' => ['nullable', 'array'],
            'visibility_scope.*' => ['string', Rule::in(array_keys(self::SCOPES))],
        ]);
        $user = $request->user();
        $target = User::query()->where('email', strtolower($data['email']))->where('status', 'active')->first();

        if (! $target) {
            return back()->with('success', 'Jika akun tujuan tersedia dan memenuhi syarat, permintaan akan muncul pada akun tersebut.');
        }
        if ($target->is($user)) {
            throw ValidationException::withMessages(['email' => 'Anda tidak dapat mengundang akun sendiri.']);
        }

        if ($user->hasRole('personal') && $target->hasRole('teacher')) {
            [$learner, $mentor] = [$user, $target];
        } elseif ($user->hasRole('teacher') && $target->hasRole('personal')) {
            [$learner, $mentor] = [$target, $user];
        } else {
            throw ValidationException::withMessages([
                'email' => 'Bimbingan privat hanya dapat menghubungkan akun Personal dengan akun Guru/Ustadz aktif.',
            ]);
        }

        $profile = $learner->personalProfile;
        if ($profile && in_array($profile->age_group, ['child', 'teen'], true) && ! $this->hasActiveGuardian($learner)) {
            throw ValidationException::withMessages([
                'email' => 'Akun di bawah 18 tahun harus lebih dahulu memiliki hubungan Orang Tua/Wali yang telah disetujui.',
            ]);
        }

        $scopes = array_values(array_unique($data['visibility_scope'] ?? ['progress_summary', 'goals']));
        UserRelationship::updateOrCreate(
            [
                'context_key' => 'private-mentorship',
                'from_user_id' => $learner->id,
                'to_user_id' => $mentor->id,
                'relationship_type' => 'mentor_learner',
            ],
            [
                'institution_id' => null,
                'created_by_user_id' => $user->id,
                'status' => 'pending',
                'visibility_scope' => $scopes,
                'starts_at' => null,
                'ends_at' => null,
                'accepted_at' => null,
            ],
        );

        return back()->with('success', 'Undangan bimbingan privat dikirim. Tidak ada data yang dibuka sebelum pihak lain menyetujui.');
    }

    public function respond(Request $request, UserRelationship $relationship): RedirectResponse
    {
        $this->authorizeRelationship($request->user(), $relationship);
        abort_unless($relationship->relationship_type === 'mentor_learner' && $relationship->status === 'pending', 422);
        abort_if((int) $relationship->created_by_user_id === (int) $request->user()->id, 403, 'Persetujuan harus diberikan oleh pihak lain.');
        $data = $request->validate(['decision' => ['required', Rule::in(['accepted', 'rejected'])]]);

        $relationship->update([
            'status' => $data['decision'],
            'accepted_at' => $data['decision'] === 'accepted' ? now() : null,
            'starts_at' => $data['decision'] === 'accepted' ? now() : null,
            'ends_at' => $data['decision'] === 'rejected' ? now() : null,
        ]);

        return back()->with('success', $data['decision'] === 'accepted' ? 'Bimbingan privat diaktifkan.' : 'Undangan bimbingan ditolak.');
    }

    public function updateConsent(Request $request, UserRelationship $relationship): RedirectResponse
    {
        $this->authorizeRelationship($request->user(), $relationship);
        abort_unless($relationship->relationship_type === 'mentor_learner' && $relationship->status === 'accepted', 422);
        abort_unless((int) $relationship->from_user_id === (int) $request->user()->id, 403, 'Hanya pemilik data Personal yang dapat mengubah batas akses.');
        $data = $request->validate([
            'visibility_scope' => ['required', 'array', 'min:1'],
            'visibility_scope.*' => ['string', Rule::in(array_keys(self::SCOPES))],
        ]);
        $relationship->update(['visibility_scope' => array_values(array_unique($data['visibility_scope']))]);

        return back()->with('success', 'Batas akses Ustadz diperbarui. Jurnal pribadi dan isi portofolio tetap tertutup.');
    }

    public function storeSession(Request $request, UserRelationship $relationship): RedirectResponse
    {
        $this->authorizeRelationship($request->user(), $relationship);
        abort_unless($relationship->relationship_type === 'mentor_learner' && $relationship->status === 'accepted', 422);
        $data = $request->validate([
            'focus' => ['required', 'string', 'max:180'],
            'learner_note' => ['nullable', 'string', 'max:2000'],
            'scheduled_at' => ['nullable', 'date', 'after_or_equal:now'],
            'duration_minutes' => ['nullable', 'integer', Rule::in([20, 30, 45, 60, 90])],
        ]);
        $isMentor = (int) $request->user()->id === (int) $relationship->to_user_id;

        MentorshipSession::create([
            'user_relationship_id' => $relationship->id,
            'learner_user_id' => $relationship->from_user_id,
            'mentor_user_id' => $relationship->to_user_id,
            'requested_by_user_id' => $request->user()->id,
            'focus' => $data['focus'],
            'learner_note' => $data['learner_note'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'status' => $isMentor && ! empty($data['scheduled_at']) ? 'scheduled' : 'requested',
        ]);

        return back()->with('success', $isMentor ? 'Sesi bimbingan dicatat.' : 'Permintaan sesi dikirim kepada Ustadz.');
    }

    public function updateSession(Request $request, MentorshipSession $session): RedirectResponse
    {
        $session->loadMissing('relationship');
        $this->authorizeRelationship($request->user(), $session->relationship);
        abort_unless($session->relationship->status === 'accepted', 422);
        $data = $request->validate([
            'status' => ['required', Rule::in(['scheduled', 'completed', 'cancelled'])],
            'scheduled_at' => ['nullable', 'date'],
            'duration_minutes' => ['nullable', 'integer', Rule::in([20, 30, 45, 60, 90])],
            'mentor_note' => ['nullable', 'string', 'max:3000'],
        ]);

        $isMentor = (int) $request->user()->id === (int) $session->mentor_user_id;
        if (! $isMentor && $data['status'] !== 'cancelled') {
            abort(403, 'Penjadwalan dan penyelesaian sesi hanya dapat dilakukan oleh Ustadz.');
        }
        if ($data['status'] === 'scheduled' && empty($data['scheduled_at'])) {
            throw ValidationException::withMessages(['scheduled_at' => 'Waktu sesi wajib diisi saat menjadwalkan.']);
        }

        $session->update([
            'status' => $data['status'],
            'scheduled_at' => $data['scheduled_at'] ?? $session->scheduled_at,
            'duration_minutes' => $data['duration_minutes'] ?? $session->duration_minutes,
            'mentor_note' => $isMentor ? ($data['mentor_note'] ?? $session->mentor_note) : $session->mentor_note,
            'completed_at' => $data['status'] === 'completed' ? now() : null,
            'cancelled_at' => $data['status'] === 'cancelled' ? now() : null,
        ]);

        return back()->with('success', 'Status sesi bimbingan diperbarui.');
    }

    private function authorizeRelationship(User $user, UserRelationship $relationship): void
    {
        abort_unless($relationship->hasParticipant($user), 403);
    }

    private function hasActiveGuardian(User $learner): bool
    {
        return UserRelationship::query()
            ->whereIn('relationship_type', ['guardian_child', 'family_companion'])
            ->where('status', 'accepted')
            ->where(fn ($query) => $query->where('from_user_id', $learner->id)->orWhere('to_user_id', $learner->id))
            ->exists();
    }

    private function learnerSnapshot(User $learner, array $scopes): array
    {
        $profile = $learner->personalProfile;
        $snapshot = ['profile' => $profile];

        if (in_array('progress_summary', $scopes, true)) {
            $snapshot['latest_check_in'] = PersonalCheckIn::query()->where('user_id', $learner->id)->latest('check_in_on')->first();
        }
        if (in_array('goals', $scopes, true)) {
            $snapshot['goals'] = PersonalGoal::query()->where('user_id', $learner->id)->where('status', 'active')->latest()->limit(5)->get();
        }
        if (in_array('practice', $scopes, true)) {
            $snapshot['practice'] = [
                'sessions_30_days' => PersonalPracticeEntry::query()->where('user_id', $learner->id)->whereDate('practiced_on', '>=', now()->subDays(30))->count(),
                'minutes_30_days' => (int) PersonalPracticeEntry::query()->where('user_id', $learner->id)->whereDate('practiced_on', '>=', now()->subDays(30))->sum('duration_minutes'),
            ];
        }
        if (in_array('portfolio', $scopes, true)) {
            $snapshot['portfolio'] = StudentPortfolio::query()
                ->where('created_by_user_id', $learner->id)
                ->where('visibility', 'private')
                ->select(['id', 'title', 'category', 'occurred_on'])
                ->latest('occurred_on')
                ->limit(5)
                ->get();
        }

        return $snapshot;
    }
}
