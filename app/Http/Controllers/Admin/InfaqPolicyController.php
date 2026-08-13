<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\InfaqAllocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InfaqPolicyController extends Controller
{
    public function __construct(private readonly InfaqAllocationService $policies) {}

    public function edit(Request $request): View
    {
        return view('admin.infaq.policy', ['policy' => $this->policies->activePolicy((int) $request->user()->institution_id)]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'teacher_development' => ['required', 'integer', 'min:0', 'max:100'],
            'foundation_operations' => ['required', 'integer', 'min:0', 'max:100'],
            'technology' => ['required', 'integer', 'min:0', 'max:100'],
            'scholarship' => ['required', 'integer', 'min:0', 'max:100'],
            'reason' => ['required', 'string', 'min:8', 'max:1000'],
        ]);
        $reason = $data['reason']; unset($data['reason']);
        $this->policies->replacePolicy($request->user(), collect($data)->map(fn ($value) => (int) $value * 100)->all(), $reason);

        return back()->with('success', 'Kebijakan baru aktif untuk infak umum berikutnya. Transaksi lama tidak dihitung ulang.');
    }
}
