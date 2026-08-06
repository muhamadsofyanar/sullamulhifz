<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuranAudioSource;
use App\Models\QuranAyahTiming;
use App\Models\QuranPracticePreset;
use App\Models\QuranSurah;
use App\Models\QuranVideoResource;
use App\Services\QuranAudioSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class QuranLibraryController extends Controller
{
    public function index(Request $request): View
    {
        $institutionId = $request->user()->institution_id;
        $sources = QuranAudioSource::query()->where('institution_id', $institutionId)->withCount('timings')->get();
        $sourceIds = $sources->pluck('id');
        return view('admin.quran-library.index', [
            'sources' => $sources,
            'timingCount' => QuranAyahTiming::query()->whereIn('quran_audio_source_id', $sourceIds)->count(),
            'surahCount' => QuranAyahTiming::query()->whereIn('quran_audio_source_id', $sourceIds)->distinct()->count('surah_id'),
            'pageCount' => QuranAyahTiming::query()->whereIn('quran_audio_source_id', $sourceIds)->whereNotNull('page_number')->distinct()->count('page_number'),
            'presetCount' => QuranPracticePreset::query()->where('institution_id', $institutionId)->where('status','active')->count(),
            'presets' => QuranPracticePreset::query()->with(['source','rubu','startSurah'])->where('institution_id', $institutionId)->latest()->paginate(30),
            'videos' => QuranVideoResource::query()->with('surah')->where('institution_id', $institutionId)->latest()->get(),
            'surahs' => QuranSurah::query()->whereBetween('id',[78,114])->orderByDesc('id')->get(),
        ]);
    }

    public function sync(Request $request, QuranAudioSyncService $service): RedirectResponse
    {
        try {
            $result = $service->syncInstitution($request->user()->institution);
            $message = "Sinkronisasi selesai: {$result['timings']}/564 timing ayat, {$result['pages']} halaman, {$result['presets']} preset.";
            if ($result['failed_surahs']) $message .= ' Surah perlu diulang: '.implode(', ', $result['failed_surahs']).'.';
            return back()->with('success', $message);
        } catch (Throwable $e) {
            report($e);
            return back()->with('error', 'Sinkronisasi belum berhasil: '.$e->getMessage());
        }
    }

    public function storeVideo(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required','string','max:190'],
            'source_type' => ['required', Rule::in(['youtube','direct'])],
            'video_url' => ['required','url','max:2000'],
            'thumbnail_url' => ['nullable','url','max:2000'],
            'surah_id' => ['nullable','exists:quran_surahs,id'],
            'start_verse' => ['nullable','integer','min:1'],
            'end_verse' => ['nullable','integer','min:1'],
            'start_seconds' => ['nullable','integer','min:0'],
            'end_seconds' => ['nullable','integer','min:1'],
            'default_repeat' => ['required','integer','min:1','max:100'],
            'notes' => ['nullable','string','max:3000'],
            'status' => ['required', Rule::in(['draft','published','archived'])],
        ]);
        QuranVideoResource::query()->create([...$data, 'institution_id'=>$request->user()->institution_id, 'created_by_user_id'=>$request->user()->id]);
        return back()->with('success','Video bacaan berhasil disimpan.');
    }

    public function updateVideo(Request $request, QuranVideoResource $video): RedirectResponse
    {
        abort_unless($video->institution_id === $request->user()->institution_id, 404);
        $data = $request->validate([
            'title' => ['required','string','max:190'],
            'status' => ['required', Rule::in(['draft','published','archived'])],
            'default_repeat' => ['required','integer','min:1','max:100'],
            'notes' => ['nullable','string','max:3000'],
        ]);
        $video->update($data);
        return back()->with('success','Video bacaan diperbarui.');
    }
}
