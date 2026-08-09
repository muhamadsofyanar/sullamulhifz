<?php

namespace App\Http\Controllers;

/** @phase 4.8 Family & Parent Portal */

use App\Models\FamilySupportNote;
use App\Models\PersonalCheckIn;
use App\Models\PersonalGoal;
use App\Models\PersonalPracticeEntry;
use App\Models\StudentPortfolio;
use App\Models\User;
use App\Models\UserRelationship;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FamilyPortalController extends Controller
{
    private const SCOPES = [
        'progress_summary' => 'Ringkasan kondisi belajar',
        'goals' => 'Target aktif',
        'practice' => 'Ringkasan latihan 30 hari',
        'portfolio' => 'Judul portofolio pilihan',
    ];

    public function index(Request $request): View
    {
        $user = $request->user();
        $relationships = UserRelationship::query()
            ->where('relationship_type', 'guardian_child')
            ->where(fn ($query) => $query->where('from_user_id', $user->id)->orWhere('to_user_id', $user->id))
            ->with([
                'fromUser.personalProfile',
                'toUser.personalProfile',
                'familySupportNotes' => fn ($query) => $query->where('status', 'visible')->with('author')->latest('observed_on')->latest('id'),
            ])
            ->latest()
            ->get();

        $connections = $relationships->map(function (UserRelationship $relationship) use ($user): array {
            $child = $relationship->fromUser;
            $guardian = $relationship->toUser;
            $scopes = array_values(array_intersect(array_keys(self::SCOPES), $relationship->visibility_scope ?? []));
            $isChild = (int) $user->id === (int) $child->id;

            return [
                'relationship' => $relationship,
                'child' => $child,
                'guardian' => $guardian,
                'counterpart' => $isChild ? $guardian : $child,
                'is_child' => $isChild,
                'scopes' => $scopes,
                'snapshot' => $relationship->status === 'accepted' && ! $isChild
                    ? $this->childSnapshot($child, $scopes)
                    : null,
            ];
        });

        return view('family.index', [
            'connections' => $connections,
            'scopeOptions' => self::SCOPES,
            'currentProfile' => $user->personalProfile,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email:rfc', 'max:190']]);
        $user = $request->user();
        $target = User::query()->where('email', strtolower($data['email']))->where('status', 'active')->first();

        if (! $target) {
            return back()->with('success', 'Jika akun tujuan tersedia dan memenuhi syarat, permintaan pendampingan akan muncul pada akun tersebut.');
        }
        if ($target->is($user)) {
            throw ValidationException::withMessages(['email' => 'Anda tidak dapat menghubungkan akun sendiri.']);
        }

        $userIsMinor = $this->isMinor($user);
        $targetIsMinor = $this->isMinor($target);
        if ($userIsMinor === $targetIsMinor) {
            throw ValidationException::withMessages([
                'email' => 'Hubungan anak–wali memerlukan satu akun anak/remaja dan satu akun dewasa atau Wali.',
            ]);
        }

        [$child, $guardian] = $userIsMinor ? [$user, $target] : [$target, $user];
        if (! $this->isAdultCompanion($guardian)) {
            throw ValidationException::withMessages(['email' => 'Akun pendamping harus berstatus dewasa atau memiliki peran Wali.']);
        }

        UserRelationship::updateOrCreate(
            [
                'context_key' => 'family-portal',
                'from_user_id' => $child->id,
                'to_user_id' => $guardian->id,
                'relationship_type' => 'guardian_child',
            ],
            [
                'institution_id' => null,
                'created_by_user_id' => $user->id,
                'status' => 'pending',
                'visibility_scope' => ['progress_summary', 'goals'],
                'starts_at' => null,
                'ends_at' => null,
                'accepted_at' => null,
            ],
        );

        return back()->with('success', 'Permintaan pendampingan keluarga dikirim. Data anak belum terbuka sebelum persetujuan pihak lain.');
    }

    public function respond(Request $request, UserRelationship $relationship): RedirectResponse
    {
        $this->authorizeRelationship($request->user(), $relationship);
        abort_unless($relationship->relationship_type === 'guardian_child' && $relationship->status === 'pending', 422);
        abort_if((int) $relationship->created_by_user_id === (int) $request->user()->id, 403, 'Persetujuan harus diberikan oleh pihak lain.');
        $data = $request->validate(['decision' => ['required', Rule::in(['accepted', 'rejected'])]]);

        $relationship->update([
            'status' => $data['decision'],
            'accepted_at' => $data['decision'] === 'accepted' ? now() : null,
            'starts_at' => $data['decision'] === 'accepted' ? now() : null,
            'ends_at' => $data['decision'] === 'rejected' ? now() : null,
        ]);

        return back()->with('success', $data['decision'] === 'accepted' ? 'Pendampingan keluarga diaktifkan.' : 'Permintaan pendampingan ditolak.');
    }

    public function updateConsent(Request $request, UserRelationship $relationship): RedirectResponse
    {
        $this->authorizeRelationship($request->user(), $relationship);
        abort_unless($relationship->relationship_type === 'guardian_child' && $relationship->status === 'accepted', 422);
        abort_unless((int) $relationship->from_user_id === (int) $request->user()->id, 403, 'Hanya akun anak/pemilik data yang dapat mengubah batas akses.');
        $data = $request->validate([
            'visibility_scope' => ['required', 'array', 'min:1'],
            'visibility_scope.*' => ['string', Rule::in(array_keys(self::SCOPES))],
        ]);
        $relationship->update(['visibility_scope' => array_values(array_unique($data['visibility_scope']))]);

        return back()->with('success', 'Batas informasi untuk keluarga diperbarui. Catatan refleksi pribadi tetap tertutup.');
    }

    public function storeNote(Request $request, UserRelationship $relationship): RedirectResponse
    {
        $this->authorizeRelationship($request->user(), $relationship);
        abort_unless($relationship->relationship_type === 'guardian_child' && $relationship->status === 'accepted', 422);
        $data = $request->validate([
            'note_type' => ['required', Rule::in(['encouragement', 'reflection', 'agreement'])],
            'body' => ['required', 'string', 'max:2000'],
            'observed_on' => ['required', 'date', 'before_or_equal:today'],
        ]);

        FamilySupportNote::create([
            'user_relationship_id' => $relationship->id,
            'child_user_id' => $relationship->from_user_id,
            'author_user_id' => $request->user()->id,
            'note_type' => $data['note_type'],
            'body' => $data['body'],
            'observed_on' => $data['observed_on'],
            'status' => 'visible',
        ]);

        return back()->with('success', 'Catatan dukungan keluarga disimpan dan hanya terlihat oleh hubungan ini.');
    }

    private function authorizeRelationship(User $user, UserRelationship $relationship): void
    {
        abort_unless($relationship->hasParticipant($user), 403);
    }

    private function isMinor(User $user): bool
    {
        return in_array($user->personalProfile?->age_group, ['child', 'teen'], true);
    }

    private function isAdultCompanion(User $user): bool
    {
        return $user->hasRole('guardian')
            || in_array($user->personalProfile?->age_group, ['adult', 'senior'], true);
    }

    private function childSnapshot(User $child, array $scopes): array
    {
        $snapshot = ['profile' => $child->personalProfile];
        if (in_array('progress_summary', $scopes, true)) {
            $snapshot['latest_check_in'] = PersonalCheckIn::query()->where('user_id', $child->id)->latest('check_in_on')->first();
        }
        if (in_array('goals', $scopes, true)) {
            $snapshot['goals'] = PersonalGoal::query()->where('user_id', $child->id)->where('status', 'active')->latest()->limit(5)->get();
        }
        if (in_array('practice', $scopes, true)) {
            $snapshot['practice'] = [
                'sessions_30_days' => PersonalPracticeEntry::query()->where('user_id', $child->id)->whereDate('practiced_on', '>=', now()->subDays(30))->count(),
                'minutes_30_days' => (int) PersonalPracticeEntry::query()->where('user_id', $child->id)->whereDate('practiced_on', '>=', now()->subDays(30))->sum('duration_minutes'),
            ];
        }
        if (in_array('portfolio', $scopes, true)) {
            $snapshot['portfolio'] = StudentPortfolio::query()
                ->where('created_by_user_id', $child->id)
                ->where('visibility', 'private')
                ->select(['id', 'title', 'category', 'occurred_on'])
                ->latest('occurred_on')
                ->limit(5)
                ->get();
        }

        return $snapshot;
    }
}
