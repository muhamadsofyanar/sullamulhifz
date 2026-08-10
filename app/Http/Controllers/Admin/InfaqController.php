<?php

namespace App\Http\Controllers\Admin;

/** @phase 6.0 Voluntary infaq verification */

use App\Http\Controllers\Controller;
use App\Models\InfaqTransaction;
use App\Services\InfaqService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InfaqController extends Controller
{
    public function __construct(private readonly InfaqService $infaq) {}

    public function index(Request $request): View
    {
        $transactions = InfaqTransaction::query()->with(['user', 'verifiedBy'])
            ->when(! $request->user()->hasRole('superadmin'), fn ($q) => $q->where('institution_id', $request->user()->institution_id))
            ->latest()->paginate(40);

        return view('admin.infaq.index', [
            'transactions' => $transactions,
            'summary' => $this->infaq->verifiedSummary(
                $request->user()->hasRole('superadmin') ? null : (int) $request->user()->institution_id,
            ),
            'purposes' => config('sullam.infaq.purposes'),
        ]);
    }

    public function update(Request $request, InfaqTransaction $transaction): RedirectResponse
    {
        abort_unless(
            $request->user()->hasRole('superadmin')
            || (int) $transaction->institution_id === (int) $request->user()->institution_id,
            404,
        );
        $data = $request->validate(['decision' => ['required', Rule::in(['verified', 'rejected'])]]);
        $this->infaq->verify($transaction, $request->user(), $data['decision']);

        return back()->with('success', $data['decision'] === 'verified' ? 'Infak diverifikasi dan bukti penerimaan dibuat.' : 'Transaksi ditandai ditolak.');
    }
}
