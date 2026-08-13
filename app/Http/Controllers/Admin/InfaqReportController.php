<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InfaqMonthlyReport;
use App\Services\InfaqReportService;
use App\Services\InfaqLedgerService;
use App\Services\InfaqAllocationService;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InfaqReportController extends Controller
{
    public function __construct(private readonly InfaqReportService $reports, private readonly InfaqLedgerService $ledger) {}
    public function index(Request $request): View
    {
        return view('admin.infaq.reports', ['reports' => InfaqMonthlyReport::where('institution_id', $request->user()->institution_id)->latest('period')->get()]);
    }
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['period' => ['required', 'date_format:Y-m']]);
        $report = $this->reports->lock($request->user(), $data['period']);
        ActivityLog::create(['institution_id' => $request->user()->institution_id, 'user_id' => $request->user()->id, 'action' => 'infaq.report_locked', 'subject_type' => $report::class, 'subject_id' => $report->id, 'new_values' => ['period' => $report->period, 'checksum' => $report->checksum], 'created_at' => now()]);
        return back()->with('success', 'Laporan bulanan dikunci sebagai arsip. Koreksi berikutnya harus melalui jurnal koreksi.');
    }

    public function correction(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'source_period' => ['required', 'date_format:Y-m'],
            'category' => ['required', \Illuminate\Validation\Rule::in(array_keys(InfaqAllocationService::DEFAULT_BASIS_POINTS))],
            'amount' => ['required', 'numeric', 'not_in:0'],
            'reason' => ['required', 'string', 'min:20', 'max:3000'],
        ]);
        $entry = $this->ledger->correction($request->user(), $data['category'], (float) $data['amount'], $data['reason'], $data['source_period']);
        ActivityLog::create(['institution_id' => $request->user()->institution_id, 'user_id' => $request->user()->id, 'action' => 'infaq.correction_recorded', 'subject_type' => $entry::class, 'subject_id' => $entry->id, 'new_values' => $entry->toArray(), 'reason' => $data['reason'], 'created_at' => now()]);
        return back()->with('success', 'Jurnal koreksi dicatat pada periode berjalan tanpa mengubah arsip lama.');
    }
}
