<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\BackupRun;
use App\Models\RestoreRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RecoveryController extends Controller
{
    public function index(): View
    {
        return view('admin.recovery.index', [
            'backups' => BackupRun::latest('completed_at')->limit(50)->get(),
            'requests' => RestoreRequest::with('backupRun')->latest()->limit(30)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['backup_run_id' => ['required', 'exists:backup_runs,id'], 'reason' => ['required', 'string', 'min:20', 'max:3000']]);
        $restore = RestoreRequest::create([
            'public_id' => (string) Str::uuid(), 'backup_run_id' => $data['backup_run_id'],
            'reason' => $data['reason'], 'status' => 'requested', 'requested_by_user_id' => $request->user()->id,
        ]);
        $this->audit($request, 'restore.requested', $restore, $data['reason']);
        return back()->with('success', 'Permintaan restore tercatat. Superadmin lain harus memberi persetujuan kedua.');
    }

    public function approve(Request $request, RestoreRequest $restoreRequest): RedirectResponse
    {
        $restoreRequest = DB::transaction(function () use ($request, $restoreRequest): RestoreRequest {
            $locked = RestoreRequest::query()->lockForUpdate()->findOrFail($restoreRequest->id);
            abort_unless($locked->status === 'requested', 422, 'Permintaan ini tidak menunggu persetujuan.');
            abort_if((int) $locked->requested_by_user_id === (int) $request->user()->id, 422, 'Pemohon tidak boleh menjadi pemberi persetujuan kedua.');
            $locked->update(['status' => 'approved_for_simulation', 'approved_by_user_id' => $request->user()->id, 'approved_at' => now()]);

            return $locked->refresh();
        }, 3);
        $this->audit($request, 'restore.approved_for_simulation', $restoreRequest, 'Persetujuan kedua diberikan.');
        return back()->with('success', 'Disetujui untuk simulasi di lingkungan terpisah. Produksi belum dipulihkan.');
    }

    public function simulation(Request $request, RestoreRequest $restoreRequest): RedirectResponse
    {
        $data = $request->validate([
            'checksum_ok' => ['required', 'boolean'], 'schema_ok' => ['required', 'boolean'],
            'tenant_count_ok' => ['required', 'boolean'], 'smoke_test_ok' => ['required', 'boolean'],
            'operator_note' => ['required', 'string', 'min:20', 'max:5000'],
        ]);
        $passed = collect(['checksum_ok', 'schema_ok', 'tenant_count_ok', 'smoke_test_ok'])->every(fn ($key) => (bool) $data[$key]);
        $restoreRequest = DB::transaction(function () use ($restoreRequest, $data, $passed): RestoreRequest {
            $locked = RestoreRequest::query()->lockForUpdate()->findOrFail($restoreRequest->id);
            abort_unless($locked->status === 'approved_for_simulation', 422, 'Permintaan belum disetujui untuk simulasi.');
            $locked->update([
                'status' => $passed ? 'simulation_passed' : 'simulation_failed',
                'simulation_result' => collect($data)->only(['checksum_ok', 'schema_ok', 'tenant_count_ok', 'smoke_test_ok'])->all(),
                'simulation_completed_at' => now(), 'operator_note' => $data['operator_note'],
            ]);

            return $locked->refresh();
        }, 3);
        $this->audit($request, $passed ? 'restore.simulation_passed' : 'restore.simulation_failed', $restoreRequest, $data['operator_note']);
        return back()->with($passed ? 'success' : 'error', $passed ? 'Simulasi lulus. Restore produksi tetap harus dijalankan operator di luar aplikasi.' : 'Simulasi gagal. Restore produksi dilarang.');
    }

    private function audit(Request $request, string $action, RestoreRequest $subject, string $reason): void
    {
        ActivityLog::create([
            'institution_id' => null, 'user_id' => $request->user()->id, 'action' => $action,
            'subject_type' => $subject::class, 'subject_id' => $subject->id, 'reason' => $reason,
            'ip_address' => $request->ip(), 'user_agent' => $request->userAgent(), 'created_at' => now(),
        ]);
    }
}
