<?php

namespace App\Http\Controllers;

use App\Models\AcademyBookmark;
use App\Models\QuranAudioSource;
use App\Models\QuranAyah;
use App\Models\QuranAyahTiming;
use App\Models\QuranPracticePreset;
use App\Models\QuranPracticeSession;
use App\Models\QuranReadingProgress;
use App\Models\QuranRubu;
use App\Models\QuranSurah;
use App\Services\QuranAudioSyncService;
use App\Services\QuranCorpusSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademyQuranController extends QuranPracticeController
{
    public function index(Request $request): View
    {
        $institutionId = (int) $request->user()->institution_id;
        $sources = QuranAudioSource::query()
            ->where('institution_id', $institutionId)
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->get();
        $defaultSource = $sources->firstWhere('is_default', true) ?: $sources->first();

        $audiences = ['all'];
        if ($request->user()->hasAnyRole(['superadmin', 'institution_admin', 'head'])) {
            $audiences = ['all', 'guardian', 'teacher', 'admin'];
        } else {
            if ($request->user()->hasRole('guardian')) $audiences[] = 'guardian';
            if ($request->user()->hasRole('teacher')) $audiences[] = 'teacher';
        }

        $presets = QuranPracticePreset::query()
            ->with(['source', 'rubu', 'startSurah', 'endSurah'])
            ->where('institution_id', $institutionId)
            ->where('status', 'active')
            ->whereIn('audience', array_values(array_unique($audiences)))
            ->orderByDesc('is_featured')
            ->orderBy('title')
            ->get();

        $corpusStatus = app(QuranCorpusSyncService::class)->status();
        $readingProgress = QuranReadingProgress::query()
            ->with(['surah', 'source'])
            ->where('institution_id', $institutionId)
            ->where('user_id', $request->user()->id)
            ->first();

        $defaultMushaf = collect();
        if ((int) ($corpusStatus['ayahs'] ?? 0) > 0) {
            $defaultSurahId = $readingProgress?->surah_id ?: 1;
            $defaultMushaf = QuranAyah::query()
                ->with('surah')
                ->where('surah_id', $defaultSurahId)
                ->orderBy('verse_number')
                ->limit(20)
                ->get();
        }

        return view('academy.audio-player', [
            'pageTitle' => 'Mushaf & Audio Al-Qur’an',
            'sources' => $sources,
            'defaultSource' => $defaultSource,
            'presets' => $presets,
            'featuredPresets' => $presets->where('is_featured', true)->values(),
            'surahs' => QuranSurah::query()->orderBy('id')->get(),
            'rubus' => QuranRubu::query()->where('juz_number', 30)->where('status', 'active')->orderBy('rubu_number')->get(),
            'pages' => QuranAyah::query()->whereNotNull('page_number')->distinct()->orderBy('page_number')->pluck('page_number'),
            'juzs' => QuranAyah::query()->whereNotNull('juz_number')->distinct()->orderBy('juz_number')->pluck('juz_number'),
            'hizbQuarters' => QuranAyah::query()->whereNotNull('hizb_quarter')->distinct()->orderBy('hizb_quarter')->pluck('hizb_quarter'),
            'timingCount' => $defaultSource
                ? QuranAyahTiming::query()->where('quran_audio_source_id', $defaultSource->id)->count()
                : 0,
            'expectedTimingCount' => QuranAudioSyncService::FULL_QURAN_AYAH_COUNT,
            'corpusStatus' => $corpusStatus,
            'readingProgress' => $readingProgress,
            'defaultMushaf' => $defaultMushaf,
            'recentSessions' => QuranPracticeSession::query()
                ->where('user_id', $request->user()->id)
                ->latest('started_at')
                ->limit(6)
                ->get(),
            'bookmarkedPresetIds' => AcademyBookmark::query()
                ->where('institution_id', $institutionId)
                ->where('user_id', $request->user()->id)
                ->where('bookmark_type', 'quran_preset')
                ->pluck('bookmark_id'),
            'bookmarkedAyahGlobals' => AcademyBookmark::query()
                ->where('institution_id', $institutionId)
                ->where('user_id', $request->user()->id)
                ->where('bookmark_type', 'quran_ayah')
                ->pluck('bookmark_id'),
        ]);
    }

    public function toggleAyahBookmark(Request $request, int $globalNumber): JsonResponse
    {
        $ayah = QuranAyah::query()->with('surah')->where('global_number', $globalNumber)->firstOrFail();
        $query = AcademyBookmark::query()
            ->where('institution_id', $request->user()->institution_id)
            ->where('user_id', $request->user()->id)
            ->where('bookmark_type', 'quran_ayah')
            ->where('bookmark_id', $globalNumber);

        if ($query->exists()) {
            $query->delete();
            return response()->json(['saved' => false, 'global_number' => $globalNumber]);
        }

        AcademyBookmark::query()->create([
            'institution_id' => $request->user()->institution_id,
            'user_id' => $request->user()->id,
            'bookmark_type' => 'quran_ayah',
            'bookmark_id' => $globalNumber,
            'label' => ($ayah->surah?->name_latin ?? 'Surah').' ayat '.$ayah->verse_number,
            'context' => [
                'surah_id' => $ayah->surah_id,
                'verse_number' => $ayah->verse_number,
                'juz_number' => $ayah->juz_number,
                'page_number' => $ayah->page_number,
            ],
        ]);

        return response()->json(['saved' => true, 'global_number' => $globalNumber]);
    }
}
