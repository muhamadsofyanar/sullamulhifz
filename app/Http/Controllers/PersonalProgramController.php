<?php

namespace App\Http\Controllers;

use App\Services\PersonalModuleAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonalProgramController extends Controller
{
    public function __construct(private readonly PersonalModuleAccessService $access) {}

    public function index(Request $request): View
    {
        return view('personal.programs', [
            'programs' => $this->access->catalog($request->user()),
        ]);
    }

    public function enroll(Request $request, string $moduleKey): RedirectResponse
    {
        $this->access->enroll($request->user(), $moduleKey);

        return back()->with('success', 'Program ditambahkan ke Ruang Personal Anda.');
    }
}
