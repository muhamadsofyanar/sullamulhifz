<?php

namespace App\Http\Controllers;

use App\Models\CommunityPost;
use App\Models\CommunitySpace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersonalCommunityController extends Controller
{
    public function index(Request $request): View
    {
        $institutionId = (int) $request->user()->institution_id;
        $spaces = CommunitySpace::query()->where('institution_id', $institutionId)
            ->where('status', 'active')->orderBy('name')->get();

        return view('personal.community', [
            'spaces' => $spaces,
            'posts' => CommunityPost::query()->with(['space', 'creator'])
                ->whereHas('space', fn ($query) => $query->where('institution_id', $institutionId)->where('status', 'active'))
                ->where('status', 'published')->latest('published_at')->limit(30)->get(),
            'myPendingPosts' => CommunityPost::query()->with('space')
                ->where('created_by_user_id', $request->user()->id)->where('status', 'pending')
                ->latest()->limit(10)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $institutionId = (int) $request->user()->institution_id;
        $data = $request->validate([
            'community_space_id' => ['required', Rule::exists('community_spaces', 'id')->where(
                fn ($query) => $query->where('institution_id', $institutionId)->where('status', 'active')
            )],
            'body' => ['required', 'string', 'min:3', 'max:3000'],
        ]);

        CommunityPost::create([
            ...$data,
            'created_by_user_id' => $request->user()->id,
            'post_type' => 'reflection',
            'status' => 'pending',
        ]);

        return back()->with('success', 'Tulisan dikirim untuk moderasi. Jurnal privat Anda tidak ikut dibagikan.');
    }
}
