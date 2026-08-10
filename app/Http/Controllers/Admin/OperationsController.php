<?php

namespace App\Http\Controllers\Admin;

/** @phase 5.1 SaaS Production Readiness */

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\OperationalCheckRun;
use App\Services\SaasReadinessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OperationsController extends Controller
{
    public function __construct(private readonly SaasReadinessService $readiness) {}

    public function index(Request $request): View
    {
        $checks = $this->readiness->checks((int) $request->user()->institution_id);

        return view('admin.operations.index', [
            'checks' => $checks,
            'summary' => $this->readiness->summary($checks),
            'history' => OperationalCheckRun::query()
                ->where('institution_id', $request->user()->institution_id)
                ->latest('checked_at')->limit(40)->get(),
            'auditCount' => ActivityLog::query()
                ->where('institution_id', $request->user()->institution_id)
                ->where('created_at', '>=', now()->subDays(30))->count(),
        ]);
    }

    public function run(Request $request): RedirectResponse
    {
        $checks = $this->readiness->persist($request->user());
        $summary = $this->readiness->summary($checks);

        return back()->with('success', $summary['critical_ready']
            ? 'Pemeriksaan operasional selesai tanpa kegagalan kritis.'
            : 'Pemeriksaan menemukan kegagalan kritis. Lihat detail sebelum rilis.');
    }
}
