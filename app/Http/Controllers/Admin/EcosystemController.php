<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\CommunitySpace;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Services\CommunityModerationService;
use App\Services\PaymentLedgerService;
use App\Services\PersonalModuleAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EcosystemController extends Controller
{
    public function __construct(
        private readonly PersonalModuleAccessService $modules,
        private readonly CommunityModerationService $moderation,
        private readonly PaymentLedgerService $payments,
    ) {}

    public function index(Request $request): View
    {
        $institutionId = (int) $request->user()->institution_id;
        $personalUsers = User::query()->with(['personalProfile', 'personalModuleEnrollments'])
            ->where('institution_id', $institutionId)
            ->whereHas('roles', fn ($query) => $query->where('roles.name', 'personal'))
            ->orderBy('name')->limit(200)->get();

        return view('admin.ecosystem.index', [
            'personalUsers' => $personalUsers,
            'personalAccessMaps' => $personalUsers->mapWithKeys(fn (User $user): array => [$user->id => $this->modules->accessMap($user)]),
            'moduleDefinitions' => $this->modules->definitions(),
            'pendingPosts' => CommunityPost::query()->with(['space', 'creator'])
                ->whereHas('space', fn ($query) => $query->where('institution_id', $institutionId))
                ->where('status', 'pending')->oldest()->limit(50)->get(),
            'communitySpaces' => CommunitySpace::query()->where('institution_id', $institutionId)->orderBy('name')->get(),
            'pendingPayments' => PaymentTransaction::query()->with('user')
                ->where('institution_id', $institutionId)->where('status', 'pending')
                ->oldest()->limit(50)->get(),
            'destination' => $this->payments->bankTransferDestination(),
        ]);
    }

    public function storeSpace(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);
        CommunitySpace::create($data + [
            'institution_id' => $request->user()->institution_id,
            'space_type' => 'institution', 'moderation_mode' => 'approval', 'status' => 'draft',
            'settings' => ['child_media_default' => 'deny', 'comments_enabled' => false],
        ]);

        return back()->with('success', 'Ruang community dibuat sebagai draft. Aktifkan setelah moderator dan kebijakan siap.');
    }

    public function updateSpace(Request $request, CommunitySpace $space): RedirectResponse
    {
        abort_unless((int) $space->institution_id === (int) $request->user()->institution_id, 404);
        $data = $request->validate(['status' => ['required', Rule::in(['draft', 'active', 'archived'])]]);
        $space->update($data);

        return back()->with('success', 'Status ruang community diperbarui.');
    }

    public function access(Request $request, User $user): RedirectResponse
    {
        abort_unless((int) $user->institution_id === (int) $request->user()->institution_id, 404);
        $data = $request->validate([
            'programs' => ['nullable', 'array'],
            'programs.*' => ['string', 'distinct', Rule::in(array_keys($this->modules->definitions()))],
        ]);
        $this->modules->syncAssignedAccess($user, $data['programs'] ?? [], $request->user());

        return back()->with('success', "Akses Ruang Personal {$user->name} diperbarui.");
    }

    public function moderate(Request $request, CommunityPost $post): RedirectResponse
    {
        $post->loadMissing('space');
        abort_unless((int) $post->space->institution_id === (int) $request->user()->institution_id, 404);
        $data = $request->validate([
            'action' => ['required', Rule::in(['approve', 'reject', 'hide'])],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);
        $this->moderation->moderate($post, $request->user(), $data['action'], $data['reason'] ?? null, 'v4.0');

        return back()->with('success', 'Keputusan moderasi tersimpan dalam audit.');
    }

    public function payment(Request $request, PaymentTransaction $transaction): RedirectResponse
    {
        abort_unless((int) $transaction->institution_id === (int) $request->user()->institution_id, 404);
        $data = $request->validate([
            'decision' => ['required', Rule::in(['paid', 'rejected'])],
            'reason' => ['nullable', 'required_if:decision,rejected', 'string', 'max:1000'],
        ]);

        if ($data['decision'] === 'paid') {
            $this->payments->markPaid(
                $transaction,
                'manual_bsi',
                'BSI-'.$transaction->id,
                ['verified_by_user_id' => $request->user()->id],
            );
            $transaction->update(['verified_by_user_id' => $request->user()->id, 'verified_at' => now(), 'rejection_reason' => null]);
        } else {
            $transaction->update([
                'status' => 'rejected', 'verified_by_user_id' => $request->user()->id,
                'verified_at' => now(), 'rejection_reason' => $data['reason'],
            ]);
        }

        return back()->with('success', 'Status pembayaran diperbarui.');
    }
}
