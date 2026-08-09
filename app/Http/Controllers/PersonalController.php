<?php

namespace App\Http\Controllers;

/** @phase 4.5 Personal 2.0 — identity, aspiration, and safeguarding */

use App\Models\PersonalGoal;
use App\Models\PersonalCheckIn;
use App\Models\PersonalPracticeEntry;
use App\Models\PersonalProfile;
use App\Models\QuranSurah;
use App\Services\PersonalJourneyService;
use App\Services\PersonalModuleAccessService;
use App\Support\PersonalIdentity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Support\Facades\Schema;

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
        $activeModules = $this->modules->activeModules($request->user());
        $primaryModule = $activeModules->first();

        return view('personal.dashboard', [
            'profile' => $profile->load('targetSurah'),
            'surahs' => QuranSurah::query()->orderBy('id')->get(['id','name_latin','verse_count']),
            'activeModules' => $activeModules,
            'primaryModule' => $primaryModule,
            'todayCheckIn' => Schema::hasTable('personal_check_ins')
                ? PersonalCheckIn::query()->where('personal_profile_id', $profile->id)->whereDate('check_in_on', today())->first()
                : null,
            'unreadNotifications' => $request->user()->unreadNotifications()->count(),
            'ageGroups' => PersonalIdentity::ageGroups(),
            'interestOptions' => PersonalIdentity::interests(),
            'learningModes' => PersonalIdentity::learningModes(),
            ...$snapshot,
        ]);
    }

    public function onboarding(Request $request): RedirectResponse
    {
        $profile = $this->profile($request);
        $minorSelected = PersonalIdentity::isMinor($request->input('age_group'));
        $data = $request->validate([
            'experience_level' => ['required', Rule::in(['starting','restarting','active','maintaining'])],
            'primary_focus' => ['required', Rule::in(['memorization','murajaah','balanced'])],
            'daily_minutes' => ['required', 'integer', 'min:5', 'max:180'],
            'target_juz' => ['nullable', 'integer', 'between:1,30'],
            'target_surah_id' => ['nullable', 'integer', 'exists:quran_surahs,id'],
            'target_date' => ['nullable', 'date', 'after:today'],
            'age_group' => ['required', Rule::in(array_keys(PersonalIdentity::ageGroups()))],
            'interests' => ['sometimes', 'array', 'max:5'],
            'interests.*' => ['string', 'distinct', Rule::in(array_keys(PersonalIdentity::interests()))],
            'aspiration' => ['nullable', 'string', 'max:150'],
            'quranic_purpose' => ['required', 'string', 'max:500'],
            'learning_mode' => ['required', Rule::in(array_keys(PersonalIdentity::learningModes()))],
            'guardian_acknowledgement' => [Rule::requiredIf($minorSelected), 'nullable', 'accepted'],
        ]);

        unset($data['guardian_acknowledgement']);
        $data['interests'] = array_values($data['interests'] ?? []);
        $data['safeguarding_acknowledged_at'] = $minorSelected ? now() : null;

        $profile->update([...$data, 'onboarding_completed_at' => now()]);
        return back()->with('success', 'Arah, cita-cita, dan ritme perjalanan tersimpan. Semuanya bisa diperbarui kapan saja.');
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
