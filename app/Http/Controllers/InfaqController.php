<?php

namespace App\Http\Controllers;

/** @phase 6.0 Voluntary infaq */

use App\Models\InfaqTransaction;
use App\Models\ActivityLog;
use App\Services\InfaqService;
use App\Services\MediaStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InfaqController extends Controller
{
    public function __construct(private readonly InfaqService $infaq, private readonly MediaStorageService $media) {}

    public function index(Request $request): View
    {
        return view('infaq.index', [
            'transactions' => InfaqTransaction::query()->where('user_id', $request->user()->id)->latest()->limit(30)->get(),
            'summary' => $this->infaq->verifiedSummary($request->user()->institution_id),
            'destination' => config('sullam.payment.bank_transfer'),
            'purposes' => config('sullam.infaq.purposes'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $purposeKeys = array_keys((array) config('sullam.infaq.purposes'));
        $data = $request->validate([
            'idempotency_key' => ['required', 'uuid', 'max:80'],
            'purpose' => ['required', Rule::in($purposeKeys)],
            'amount' => ['required', 'numeric', 'min:1000', 'max:1000000000'],
            'is_anonymous' => ['nullable', 'boolean'],
            'show_donor_name' => ['nullable', 'boolean'],
            'transfer_proof' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:'.config('sullam.infaq.proof_max_kb', 5120)],
            'voluntary_acknowledgement' => ['accepted'],
        ]);
        $existing = InfaqTransaction::query()
            ->where('user_id', $request->user()->id)
            ->where('idempotency_key', $data['idempotency_key'])
            ->first();
        $proof = null;
        if (! $existing && $request->hasFile('transfer_proof')) {
            $proof = $this->media->store(
                $request->file('transfer_proof'), $request->user(), 'infaq/transfer-proofs', 'private', 3650,
            );
            $data['transfer_proof_media_asset_id'] = $proof->id;
        } elseif ($existing) {
            $data['transfer_proof_media_asset_id'] = $existing->transfer_proof_media_asset_id;
        }
        try {
            $transaction = $this->infaq->createPending($request->user(), $data, $data['idempotency_key']);
        } catch (\Throwable $exception) {
            if ($proof) {
                $this->media->delete($proof);
            }
            throw $exception;
        }

        return back()->with('success', "Niat infak {$transaction->public_id} tercatat. Transfer bersifat sukarela dan akses aplikasi Anda tidak berubah.");
    }

    public function consent(Request $request, InfaqTransaction $transaction): RedirectResponse
    {
        abort_unless((int) $transaction->user_id === (int) $request->user()->id, 404);
        $data = $request->validate(['show_donor_name' => ['required', 'boolean']]);
        $show = (bool) $data['show_donor_name'];
        $before = ['show_donor_name' => $transaction->show_donor_name, 'donor_consent_at' => $transaction->donor_consent_at?->toIso8601String()];
        $transaction->update(['show_donor_name' => $show, 'is_anonymous' => ! $show, 'donor_consent_at' => $show ? now() : null]);
        ActivityLog::create([
            'institution_id' => $transaction->institution_id, 'user_id' => $request->user()->id,
            'action' => $show ? 'infaq.donor_name_consent_granted' : 'infaq.donor_name_consent_revoked',
            'subject_type' => $transaction::class, 'subject_id' => $transaction->id,
            'old_values' => $before,
            'new_values' => ['show_donor_name' => $show, 'donor_consent_at' => $transaction->fresh()->donor_consent_at?->toIso8601String()],
            'ip_address' => $request->ip(), 'user_agent' => $request->userAgent(), 'created_at' => now(),
        ]);

        return back()->with('success', $show ? 'Persetujuan penayangan nama disimpan.' : 'Persetujuan dicabut. Nama kembali anonim pada laporan berikutnya.');
    }
}
