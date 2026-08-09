<?php

namespace App\Http\Controllers;

use App\Models\PersonalGoal;
use App\Models\PersonalPracticeEntry;
use App\Models\PersonalProfile;
use App\Models\QuranSurah;
use App\Services\PersonalJourneyService;
use App\Services\PersonalModuleAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersonalController extends Controller
{
    public function __construct(
        private readonly PersonalJourneyService $journey,
        private readonly PersonalModuleAccessService $modules,
    ) {}

    public function index(Request $request): View
    {
        $profile = $this->profile($request);
        $snapshot = $this->journey->snapshot($request->user(), $profile);

        return view('personal.dashboard', [
            'profile' => $profile->load('targetSurah'),
            'surahs' => QuranSurah::query()->orderBy('id')->get(['id','name_latin','verse_count']),
            'activeModules' => $this->modules->activeModules($request->user()),
            ...$snapshot,
        ]);
    }

    public function onboarding(Request $request): RedirectResponse
    {
        $profile = $this->profile($request);
        $data = $request->validate([
            'experience_level' => ['required', Rule::in(['starting','restarting','active','maintaining'])],
            'primary_focus' => ['required', Rule::in(['memorization','murajaah','balanced'])],
            'daily_minutes' => ['required', 'integer', 'min:5', 'max:180'],
            'target_juz' => ['nullable', 'integer', 'between:1,30'],
            'target_surah_id' => ['nullable', 'integer', 'exists:quran_surahs,id'],
            'target_date' => ['nullable', 'date', 'after:today'],
        ]);

        $profile->update([...$data, 'onboarding_completed_at' => now()]);
        return back()->with('success', 'Arah perjalanan tersimpan. Ritme ini bisa Anda ubah kapan saja.');
    }

    public function storeActivity(Request $request): RedirectResponse
    {
        $profile = $this->profile($request);
        $data = $request->validate([
            'activity_type' => ['required', Rule::in(['memorization','murajaah','tilawah','reflection'])],
            'surah_id' => ['nullable', 'required_unless:activity_type,reflection', 'integer', 'exists:quran_surahs,id'],
            'start_verse' => ['nullable', 'required_with:surah_id', 'integer', 'min:1'],
            'end_verse' => ['nullable', 'required_with:surah_id', 'integer', 'gte:start_verse'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:600'],
            'self_rating' => ['nullable', Rule::in(['needs_review','developing','steady','strong'])],
            'notes' => ['nullable', 'string', 'max:2000'],
            'practiced_on' => ['required', 'date', 'before_or_equal:today'],
        ]);

        if (! empty($data['surah_id'])) {
            $surah = QuranSurah::query()->findOrFail($data['surah_id']);
            if ((int) $data['end_verse'] > (int) $surah->verse_count) {
                return back()->withErrors(['end_verse' => "Surah {$surah->name_latin} memiliki {$surah->verse_count} ayat."])->withInput();
            }
        }

        PersonalPracticeEntry::create([
            ...$data,
            'institution_id' => $request->user()->institution_id,
            'personal_profile_id' => $profile->id,
            'user_id' => $request->user()->id,
        ]);
        $this->journey->refreshActiveGoals($request->user());

        return back()->with('success', 'Aktivitas tersimpan. Yang kita kejar adalah jejak yang jujur dan konsisten.');
    }

    public function storeGoal(Request $request): RedirectResponse
    {
        $profile = $this->profile($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'metric' => ['required', Rule::in(['practice_minutes','sessions','active_days','memorization_verses','murajaah_verses'])],
            'target_value' => ['required', 'integer', 'min:1', 'max:100000'],
            'starts_on' => ['required', 'date'],
            'due_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
        ]);

        $goal = PersonalGoal::create([
            ...$data,
            'institution_id' => $request->user()->institution_id,
            'personal_profile_id' => $profile->id,
            'user_id' => $request->user()->id,
            'status' => 'active',
        ]);
        $this->journey->refreshGoal($goal);

        return back()->with('success', 'Target baru ditambahkan. Buat cukup menantang, tetapi tetap manusiawi.');
    }

    public function completeGoal(Request $request, PersonalGoal $goal): RedirectResponse
    {
        abort_unless(
            (int) $goal->user_id === (int) $request->user()->id
            && (int) $goal->institution_id === (int) $request->user()->institution_id,
            404
        );
        $goal->update(['status'=>'completed', 'completed_at'=>now()]);
        return back()->with('success', 'Target ditandai selesai.');
    }

    private function profile(Request $request): PersonalProfile
    {
        return PersonalProfile::query()
            ->where('institution_id', $request->user()->institution_id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
    }
}
