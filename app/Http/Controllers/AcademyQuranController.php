<?php

namespace App\Http\Controllers;

use App\Models\QuranAudioSource;
use App\Models\QuranAyahTiming;
use App\Models\QuranPracticePreset;
use App\Models\QuranRubu;
use App\Models\QuranSurah;
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

        $pages = $defaultSource
            ? QuranAyahTiming::query()
                ->where('quran_audio_source_id', $defaultSource->id)
                ->whereNotNull('page_number')
                ->distinct()
                ->orderBy('page_number')
                ->pluck('page_number')
            : collect();

        return view('academy.audio-player', [
            'pageTitle' => 'Audio & Latihan Al-Qur’an',
            'sources' => $sources,
            'defaultSource' => $defaultSource,
            'presets' => $presets,
            'featuredPresets' => $presets->where('is_featured', true)->values(),
            'surahs' => QuranSurah::query()->whereBetween('id', [78, 114])->orderByDesc('id')->get(),
            'rubus' => QuranRubu::query()->where('juz_number', 30)->where('status', 'active')->orderBy('rubu_number')->get(),
            'pages' => $pages,
            'timingCount' => $defaultSource
                ? QuranAyahTiming::query()->where('quran_audio_source_id', $defaultSource->id)->whereBetween('surah_id', [78, 114])->count()
                : 0,
            'recentSessions' => \App\Models\QuranPracticeSession::query()
                ->where('user_id', $request->user()->id)
                ->latest('started_at')
                ->limit(6)
                ->get(),
            'bookmarkedPresetIds' => \App\Models\AcademyBookmark::query()
                ->where('institution_id', $institutionId)
                ->where('user_id', $request->user()->id)
                ->where('bookmark_type', 'quran_preset')
                ->pluck('bookmark_id'),
        ]);
    }
}
