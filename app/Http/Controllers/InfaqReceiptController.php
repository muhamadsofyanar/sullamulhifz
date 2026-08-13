<?php

namespace App\Http\Controllers;

use App\Models\InfaqTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InfaqReceiptController extends Controller
{
    public function show(Request $request, InfaqTransaction $transaction): View
    {
        $allowed = (int) $transaction->user_id === (int) $request->user()->id
            || ($request->user()->hasPermission('infaq.audit.view') && ((int) $transaction->institution_id === (int) $request->user()->institution_id || $request->user()->hasRole('superadmin')));
        abort_unless($allowed && $transaction->status === 'verified', 404);

        return view('infaq.receipt', ['transaction' => $transaction->load(['institution', 'verifiedBy'])]);
    }
}
