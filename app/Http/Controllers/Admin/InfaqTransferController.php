<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InfaqFundTransfer;
use App\Models\ActivityLog;
use App\Services\InfaqAllocationService;
use App\Services\InfaqLedgerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InfaqTransferController extends Controller
{
    public function __construct(private readonly InfaqLedgerService $ledger) {}

    public function store(Request $request): RedirectResponse
    {
        $categories = array_keys(InfaqAllocationService::DEFAULT_BASIS_POINTS);
        $data = $request->validate([
            'from_category' => ['required', Rule::in($categories), 'different:to_category'],
            'to_category' => ['required', Rule::in($categories)],
            'amount' => ['required', 'numeric', 'min:1'], 'reason' => ['required', 'string', 'min:20', 'max:3000'],
        ]);
        $transfer = InfaqFundTransfer::create([
            ...$data, 'public_id' => (string) Str::uuid(), 'institution_id' => $request->user()->institution_id,
            'status' => 'submitted', 'created_by_user_id' => $request->user()->id,
        ]);
        ActivityLog::create(['institution_id' => $request->user()->institution_id, 'user_id' => $request->user()->id, 'action' => 'infaq.transfer_submitted', 'subject_type' => $transfer::class, 'subject_id' => $transfer->id, 'new_values' => $transfer->toArray(), 'reason' => $data['reason'], 'created_at' => now()]);

        return back()->with('success', 'Pemindahan dana diajukan dan belum mengubah saldo.');
    }

    public function review(Request $request, InfaqFundTransfer $fundTransfer): RedirectResponse
    {
        $data = $request->validate(['decision' => ['required', Rule::in(['approved', 'rejected'])], 'review_note' => ['required', 'string', 'min:8', 'max:2000']]);
        abort_unless($request->user()->hasRole('superadmin') || (int) $fundTransfer->institution_id === (int) $request->user()->institution_id, 404);
        abort_if((int) $fundTransfer->created_by_user_id === (int) $request->user()->id, 422, 'Pengaju tidak boleh memeriksa pemindahannya sendiri.');
        if ($data['decision'] === 'approved') {
            $fundTransfer = $this->ledger->approveTransfer($fundTransfer, $request->user(), $data['review_note']);
        } else {
            $fundTransfer = DB::transaction(function () use ($fundTransfer, $request, $data): InfaqFundTransfer {
                $locked = InfaqFundTransfer::query()->lockForUpdate()->findOrFail($fundTransfer->id);
                abort_unless($locked->status === 'submitted', 422, 'Pemindahan ini sudah selesai diperiksa.');
                abort_if((int) $locked->created_by_user_id === (int) $request->user()->id, 422, 'Pengaju tidak boleh memeriksa pemindahannya sendiri.');
                $locked->update(['status' => 'rejected', 'approved_by_user_id' => $request->user()->id, 'approved_at' => now(), 'review_note' => $data['review_note']]);

                return $locked->refresh();
            }, 3);
        }
        ActivityLog::create(['institution_id' => $fundTransfer->institution_id, 'user_id' => $request->user()->id, 'action' => 'infaq.transfer_'.$data['decision'], 'subject_type' => $fundTransfer::class, 'subject_id' => $fundTransfer->id, 'new_values' => $fundTransfer->fresh()->toArray(), 'reason' => $data['review_note'], 'created_at' => now()]);

        return back()->with('success', $data['decision'] === 'approved' ? 'Pemindahan disetujui dan dicatat sebagai debit-kredit berimbang.' : 'Pemindahan ditolak.');
    }
}
