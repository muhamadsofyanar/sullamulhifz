<?php

namespace App\Http\Controllers;

/** @phase 4.9 Learning & Academy Integration — one learning space for Personal */

use App\Services\UnifiedLearningHubService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonalLearningHubController extends Controller
{
    public function __construct(private readonly UnifiedLearningHubService $hub) {}

    public function index(Request $request): View
    {
        return view('personal.learning-hub', $this->hub->snapshot($request->user()));
    }
}
