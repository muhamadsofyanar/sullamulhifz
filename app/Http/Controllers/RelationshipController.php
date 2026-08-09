<?php

namespace App\Http\Controllers;

/** @phase 4.3 Identity & Relationship Core; @phase 4.6 Private Ustadz; @phase 4.8 Family & Parent Portal */

use App\Models\User;
use App\Models\UserRelationship;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RelationshipController extends Controller
{
    private const TYPES = [
        'mentor_learner' => 'Ustadz membimbing personal',
        'guardian_child' => 'Orang tua/wali mendampingi anak',
        'family_companion' => 'Pendamping keluarga',
    ];

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('relationships.index', [
            'types' => self::TYPES,
            'creatableTypes' => ['family_companion' => self::TYPES['family_companion']],
            'outgoing' => $user->outgoingRelationships()->with('toUser')->latest()->get(),
            'incoming' => $user->incomingRelationships()->with('fromUser')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email:rfc', 'max:190'],
            'relationship_type' => ['required', Rule::in(['family_companion'])],
        ]);

        $target = User::query()->where('email', strtolower($data['email']))->where('status', 'active')->first();
        if (! $target) {
            return back()->with('success', 'Jika akun tujuan tersedia dan memenuhi syarat, permintaan akan muncul pada akun tersebut.');
        }
        if ($target->is($request->user())) {
            return back()->withErrors(['email' => 'Hubungan tidak dapat dibuat dengan akun sendiri.'])->withInput();
        }

        $workspace = $request->attributes->get('workspace');
        $institutionId = $workspace?->workspace_type === 'personal' ? null : $workspace?->id;
        $contextKey = $institutionId ? 'workspace:'.$institutionId : 'global';
        UserRelationship::updateOrCreate(
            [
                'context_key' => $contextKey,
                'from_user_id' => $request->user()->id,
                'to_user_id' => $target->id,
                'relationship_type' => $data['relationship_type'],
            ],
            [
                'institution_id' => $institutionId,
                'created_by_user_id' => $request->user()->id,
                'status' => 'pending',
                'visibility_scope' => ['progress_summary'],
                'starts_at' => null,
                'ends_at' => null,
                'accepted_at' => null,
            ],
        );

        return back()->with('success', 'Permintaan hubungan dikirim. Akses baru aktif setelah disetujui akun tujuan.');
    }

    public function respond(Request $request, UserRelationship $relationship): RedirectResponse
    {
        abort_unless($relationship->hasParticipant($request->user()), 403);
        abort_if((int) $relationship->created_by_user_id === (int) $request->user()->id, 403, 'Persetujuan harus diberikan oleh pihak lain.');
        abort_unless($relationship->status === 'pending', 422, 'Permintaan ini sudah diproses.');
        $data = $request->validate(['decision' => ['required', Rule::in(['accepted', 'rejected'])]]);

        $relationship->update([
            'status' => $data['decision'],
            'accepted_at' => $data['decision'] === 'accepted' ? now() : null,
            'starts_at' => $data['decision'] === 'accepted' ? now() : null,
        ]);

        return back()->with('success', $data['decision'] === 'accepted' ? 'Hubungan disetujui.' : 'Permintaan ditolak.');
    }

    public function destroy(Request $request, UserRelationship $relationship): RedirectResponse
    {
        abort_unless(in_array($request->user()->id, [$relationship->from_user_id, $relationship->to_user_id], true), 403);
        $relationship->update(['status' => 'ended', 'ends_at' => now()]);

        return back()->with('success', 'Hubungan diakhiri tanpa menghapus riwayat.');
    }
}
