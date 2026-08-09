<?php

namespace App\Http\Controllers;

/** @phase 4.5 Personal 2.0 — aspiration-aware journey and private portfolio */

use App\Models\PersonalCheckIn;
use App\Models\PersonalPracticeEntry;
use App\Models\QuranGuidedSubmission;
use App\Models\QuranPracticeSession;
use App\Models\QuranProgramProgress;
use App\Models\StudentPortfolio;
use App\Services\PersonalModuleAccessService;
use App\Support\PersonalIdentity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PersonalExperienceController extends Controller
{
    public function __construct(private readonly PersonalModuleAccessService $modules) {}

    public function journey(Request $request): View
    {
        $user = $request->user();
        $profile = $user->personalProfile()->firstOrFail();
        $timeline = collect();
        $portfolioEntries = Schema::hasTable('student_portfolios')
            ? StudentPortfolio::query()
                ->where('institution_id', $user->institution_id)
                ->where('student_id', $profile->student_id)
                ->where('status', 'published')
                ->latest('occurred_on')
                ->latest('id')
                ->limit(20)
                ->get()
            : collect();

        if (Schema::hasTable('personal_practice_entries')) {
            $timeline = $timeline->concat(PersonalPracticeEntry::query()
                ->with('surah')->where('institution_id', $user->institution_id)->where('user_id', $user->id)
                ->latest('practiced_on')->limit(30)->get()->map(fn ($entry): array => [
                    'date' => $entry->practiced_on,
                    'type' => 'Jurnal pribadi',
                    'title' => match ($entry->activity_type) {
                        'memorization' => 'Hafalan baru', 'murajaah' => 'Murāja‘ah',
                        'tilawah' => 'Tilawah', default => 'Refleksi',
                    },
                    'detail' => trim(($entry->surah?->name_latin ? $entry->surah->name_latin.' · ' : '').$entry->duration_minutes.' menit'),
                    'status' => $entry->self_rating,
                ]));
        }

        if (Schema::hasTable('quran_practice_sessions')) {
            $timeline = $timeline->concat(QuranPracticeSession::query()
                ->where('institution_id', $user->institution_id)->where('user_id', $user->id)
                ->latest('started_at')->limit(20)->get()->map(fn ($session): array => [
                    'date' => $session->started_at,
                    'type' => 'Latihan Qur’an',
                    'title' => $session->status === 'completed' ? 'Sesi latihan selesai' : 'Sesi latihan dimulai',
                    'detail' => max(0, (int) floor(((int) $session->duration_seconds) / 60)).' menit · '.($session->mode ?: 'latihan'),
                    'status' => $session->status,
                ]));
        }

        if (Schema::hasTable('quran_guided_submissions')) {
            $timeline = $timeline->concat(QuranGuidedSubmission::query()
                ->with('surah')->where('learner_institution_id', $user->institution_id)->where('learner_user_id', $user->id)
                ->latest('submitted_at')->limit(20)->get()->map(fn ($submission): array => [
                    'date' => $submission->submitted_at,
                    'type' => 'Program Asatidz',
                    'title' => 'Setoran '.($submission->surah?->name_latin ?: 'Qur’an'),
                    'detail' => 'Ayat '.$submission->start_verse.'–'.$submission->end_verse,
                    'status' => $submission->review_status,
                ]));
        }

        if (Schema::hasTable('quran_program_progress')) {
            $timeline = $timeline->concat(QuranProgramProgress::query()
                ->whereHas('enrollment', fn ($query) => $query->where('institution_id', $user->institution_id)->where('user_id', $user->id))
                ->with('step')->latest('updated_at')->limit(20)->get()->map(fn ($progress): array => [
                    'date' => $progress->completed_at ?: $progress->started_at ?: $progress->updated_at,
                    'type' => 'Qur’an Journey',
                    'title' => $progress->step?->title ?: 'Langkah perjalanan',
                    'detail' => $progress->notes ?: 'Progres program diperbarui',
                    'status' => $progress->status,
                ]));
        }

        $timeline = $timeline->concat($portfolioEntries->map(fn (StudentPortfolio $entry): array => [
            'date' => $entry->occurred_on ?: $entry->created_at,
            'type' => 'Portofolio privat',
            'title' => $entry->title,
            'detail' => $entry->description ?: (PersonalIdentity::portfolioCategories()[$entry->category] ?? 'Jejak pertumbuhan'),
            'status' => 'tercatat',
        ]));

        return view('personal.journey', [
            'profile' => $profile,
            'timeline' => $timeline->filter(fn (array $item): bool => (bool) $item['date'])
                ->sortByDesc(fn (array $item) => $item['date'])->take(60)->values(),
            'activeModules' => $this->modules->activeModules($user),
            'checkIns' => Schema::hasTable('personal_check_ins')
                ? PersonalCheckIn::query()->where('personal_profile_id', $profile->id)->latest('check_in_on')->limit(14)->get()
                : collect(),
            'portfolioEntries' => $portfolioEntries,
            'portfolioCategories' => PersonalIdentity::portfolioCategories(),
            'learningModes' => PersonalIdentity::learningModes(),
        ]);
    }

    public function storePortfolio(Request $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->personalProfile()->firstOrFail();
        abort_unless(Schema::hasTable('student_portfolios'), 503, 'Fondasi portofolio belum tersedia.');

        $data = $request->validate([
            'category' => ['required', Rule::in(array_keys(PersonalIdentity::portfolioCategories()))],
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:2000'],
            'occurred_on' => ['required', 'date', 'before_or_equal:today'],
            'quranic_value' => ['nullable', 'string', 'max:190'],
            'aspiration_connection' => ['nullable', 'string', 'max:300'],
        ]);

        StudentPortfolio::create([
            'institution_id' => $user->institution_id,
            'student_id' => $profile->student_id,
            'created_by_user_id' => $user->id,
            'category' => $data['category'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'occurred_on' => $data['occurred_on'],
            'visibility' => 'private',
            'status' => 'published',
            'metadata' => [
                'source' => 'personal_v450',
                'quranic_value' => $data['quranic_value'] ?? null,
                'aspiration_connection' => $data['aspiration_connection'] ?? null,
            ],
        ]);

        return back()->with('success', 'Jejak portofolio privat tersimpan tanpa nilai, peringkat, atau perbandingan.');
    }

    public function checkIn(Request $request): RedirectResponse
    {
        $user = $request->user();
        $profile = $user->personalProfile()->firstOrFail();
        abort_unless(Schema::hasTable('personal_check_ins'), 503, 'Migration v4.0.0 belum dijalankan.');
        $data = $request->validate([
            'energy' => ['required', Rule::in(['low', 'steady', 'strong'])],
            'focus' => ['required', Rule::in(['memorization', 'murajaah', 'tilawah', 'reflection'])],
            'intention' => ['nullable', 'string', 'max:190'],
            'reflection' => ['nullable', 'string', 'max:2000'],
        ]);

        PersonalCheckIn::query()->updateOrCreate(
            ['personal_profile_id' => $profile->id, 'check_in_on' => today()],
            $data + ['institution_id' => $user->institution_id, 'user_id' => $user->id],
        );

        return back()->with('success', 'Check-in hari ini tersimpan. Ritme boleh disesuaikan dengan keadaan nyata.');
    }
}
