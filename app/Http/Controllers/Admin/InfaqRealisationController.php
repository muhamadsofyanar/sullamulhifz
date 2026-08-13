<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InfaqRealisation;
use App\Models\InfaqFundTransfer;
use App\Services\InfaqAllocationService;
use App\Services\InfaqLedgerService;
use App\Services\InfaqRealisationService;
use App\Services\MediaStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Notifications\InfaqRealisationStatusNotification;

class InfaqRealisationController extends Controller
{
    public function __construct(private readonly InfaqRealisationService $realisations, private readonly InfaqLedgerService $ledger, private readonly MediaStorageService $media) {}

    public function index(Request $request): View
    {
        $institutionId = (int) $request->user()->institution_id;
        $categories = array_keys(InfaqAllocationService::DEFAULT_BASIS_POINTS);
        return view('admin.infaq.realisations.index', [
            'realisations' => InfaqRealisation::with(['creator', 'reviewer', 'evidences'])->where('institution_id', $institutionId)->latest()->paginate(30),
            'categories' => $categories,
            'balances' => collect($categories)->mapWithKeys(fn ($category) => [$category => $this->ledger->balance($institutionId, $category)]),
            'fundTransfers' => InfaqFundTransfer::where('institution_id', $institutionId)->latest()->limit(20)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $categories = array_keys(InfaqAllocationService::DEFAULT_BASIS_POINTS);
        $data = $request->validate([
            'category' => ['required', Rule::in($categories)], 'program_name' => ['required', 'string', 'max:190'],
            'purpose' => ['required', 'string', 'max:3000'], 'amount' => ['required', 'numeric', 'min:1'],
            'beneficiary_count' => ['required', 'integer', 'min:0'], 'impact_summary' => ['nullable', 'string', 'max:5000'],
            'realised_on' => ['required', 'date', 'before_or_equal:today'],
            'evidence_type' => ['required', Rule::in(['receipt', 'invoice', 'accountability_letter'])],
            'original_evidence' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'public_evidence' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);
        $original = $this->media->store($request->file('original_evidence'), $request->user(), 'infaq/realisations/original', 'restricted', 3650);
        $public = $this->media->store($request->file('public_evidence'), $request->user(), 'infaq/realisations/redacted', 'restricted', 3650);
        $this->realisations->create($request->user(), $data, $original, $public);

        return back()->with('success', 'Realisasi diajukan. Penanggung Jawab Lembaga harus memeriksa sebelum dipublikasikan.');
    }

    public function review(Request $request, InfaqRealisation $realisation): RedirectResponse
    {
        $data = $request->validate(['decision' => ['required', Rule::in(['verified', 'rejected'])], 'review_note' => ['required', 'string', 'min:8', 'max:2000']]);
        $reviewed = $this->realisations->review($realisation, $request->user(), $data['decision'], $data['review_note']);
        $reviewed->creator?->notify(new InfaqRealisationStatusNotification($reviewed));

        return back()->with('success', $data['decision'] === 'verified' ? 'Realisasi diverifikasi dan masuk laporan transparansi.' : 'Realisasi dikembalikan untuk diperbaiki.');
    }

    public function resubmit(Request $request, InfaqRealisation $realisation): RedirectResponse
    {
        $data = $request->validate([
            'purpose' => ['required', 'string', 'max:3000'], 'impact_summary' => ['nullable', 'string', 'max:5000'],
            'original_evidence' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'public_evidence' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);
        $original = $request->hasFile('original_evidence') ? $this->media->store($request->file('original_evidence'), $request->user(), 'infaq/realisations/original', 'restricted', 3650) : null;
        $public = $request->hasFile('public_evidence') ? $this->media->store($request->file('public_evidence'), $request->user(), 'infaq/realisations/redacted', 'restricted', 3650) : null;
        $this->realisations->resubmit($realisation, $request->user(), $data, $original, $public);
        return back()->with('success', 'Perbaikan diajukan ulang untuk diperiksa oleh Penanggung Jawab.');
    }
}
