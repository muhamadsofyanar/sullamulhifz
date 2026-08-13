<?php

namespace App\Http\Controllers;

use App\Models\InfaqLedgerEntry;
use App\Models\InfaqRealisation;
use App\Models\InfaqTransaction;
use App\Models\Institution;
use App\Support\Feature;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicInfaqController extends Controller
{
    public function show(Institution $institution): View
    {
        abort_unless($institution->status === 'active' && Feature::enabled('v610_pilot', $institution->id), 404);
        $received = (float) InfaqTransaction::where('institution_id', $institution->id)->where('status', 'verified')->sum('amount');
        $allocated = (float) InfaqLedgerEntry::where('institution_id', $institution->id)->where('entry_type', 'receipt_credit')->sum('amount');
        $realised = abs((float) InfaqLedgerEntry::where('institution_id', $institution->id)->where('entry_type', 'realisation_debit')->sum('amount'));
        $balances = InfaqLedgerEntry::where('institution_id', $institution->id)
            ->selectRaw('category, SUM(amount) as balance')->groupBy('category')->orderBy('category')->get();
        $programs = InfaqRealisation::with(['evidences' => fn ($query) => $query->where('public_review_status', 'approved')])
            ->where('institution_id', $institution->id)->where('status', 'verified')->latest('realised_on')->limit(30)->get();
        $donors = InfaqTransaction::with('user:id,name')->where('institution_id', $institution->id)->where('status', 'verified')
            ->where('show_donor_name', true)->whereNotNull('donor_consent_at')->whereNotNull('user_id')
            ->get()->pluck('user.name')->filter()->unique()->sort()->values();

        return view('public.infaq.show', compact('institution', 'received', 'allocated', 'realised', 'balances', 'programs', 'donors'));
    }
}
