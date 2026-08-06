<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AssignmentRecipient;
use App\Models\LaunchCheck;
use App\Models\LoginHistory;
use App\Models\Meeting;
use App\Models\QuranAudioSource;
use App\Models\QuranAyahTiming;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LaunchReadinessController extends Controller
{
    public function index(Request $request): View
    {
        $institutionId = $request->user()->institution_id;
        $checks = LaunchCheck::with('checkedBy')
            ->where('institution_id', $institutionId)
            ->orderBy('category')
            ->orderBy('id')
            ->get()
            ->groupBy('category');

        $total = $checks->flatten()->count();
        $done = $checks->flatten()->where('status', 'done')->count();

        $sources = QuranAudioSource::where('institution_id', $institutionId)
            ->where('status', 'active')->get();
        $timingCount = QuranAyahTiming::whereIn('quran_audio_source_id', $sources->pluck('id'))
            ->whereBetween('surah_id', [78, 114])->count();

        return view('admin.launch-readiness.index', [
            'checks' => $checks,
            'completion' => $total > 0 ? (int) round(($done / $total) * 100) : 0,
            'stats' => [
                'students' => Student::where('institution_id', $institutionId)->where('status', 'active')->count(),
                'activeUsers' => User::where('institution_id', $institutionId)->where('status', 'active')->count(),
                'completedMeetings' => Meeting::where('institution_id', $institutionId)->where('status', 'completed')->count(),
                'pendingTasks' => AssignmentRecipient::whereHas('assignment', fn ($q) => $q->where('institution_id', $institutionId))->whereIn('status', ['assigned','submitted','revision_needed'])->count(),
                'qariSources' => $sources->count(),
                'quranTimings' => $timingCount,
            ],
            'recentLogins' => LoginHistory::with('user')->where('institution_id', $institutionId)->latest('logged_in_at')->limit(12)->get(),
            'recentActivities' => ActivityLog::where('institution_id', $institutionId)->latest('created_at')->limit(12)->get(),
        ]);
    }

    public function update(Request $request, LaunchCheck $launchCheck): RedirectResponse
    {
        abort_unless($launchCheck->institution_id === $request->user()->institution_id, 404);
        $data = $request->validate([
            'status' => ['required', Rule::in(['pending', 'in_progress', 'done', 'blocked'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $launchCheck->update([
            ...$data,
            'checked_by_user_id' => $request->user()->id,
            'checked_at' => $data['status'] === 'done' ? now() : null,
        ]);

        return back()->with('success', 'Status kesiapan peluncuran diperbarui.');
    }
}
