<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\FridayDevelopmentSession;
use App\Models\LearningGroup;
use App\Models\Level;
use App\Models\MediaAsset;
use App\Models\QuranSurah;
use App\Models\SchoolClass;
use App\Services\MediaStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function __construct(private readonly MediaStorageService $media)
    {
    }

    public function index(Request $request): View
    {
        $institutionId = $request->user()->institution_id;

        return view('admin.content.index', [
            'announcements' => Announcement::with(['schoolClass', 'learningGroup', 'targets'])
                ->where('institution_id', $institutionId)->latest()->paginate(10, ['*'], 'ann_page'),
            'fridaySessions' => FridayDevelopmentSession::with(['schoolClass', 'surah', 'targets.schoolClass', 'targets.learningGroup', 'targets.level'])
                ->where('institution_id', $institutionId)->latest('session_date')->paginate(10, ['*'], 'friday_page'),
            'classes' => SchoolClass::where('institution_id', $institutionId)->where('status', 'active')->orderBy('name')->get(),
            'groups' => LearningGroup::where('institution_id', $institutionId)->where('status', 'active')->orderBy('name')->get(),
            'levels' => Level::where('institution_id', $institutionId)->where('status', 'active')->orderBy('sequence')->get(),
            'surahs' => QuranSurah::orderBy('id')->get(),
        ]);
    }

    public function storeAnnouncement(Request $request): RedirectResponse
    {
        $institutionId = $request->user()->institution_id;
        $maxKb = (int) config('sullam.upload_max_kb', 25600);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'content' => ['required', 'string', 'max:20000'],
            'audience_type' => ['required', Rule::in(['all', 'admins', 'teachers', 'guardians', 'class', 'group', 'level'])],
            'class_id' => ['nullable', Rule::exists('classes', 'id')->where('institution_id', $institutionId)],
            'learning_group_id' => ['nullable', Rule::exists('learning_groups', 'id')->where('institution_id', $institutionId)],
            'level_id' => ['nullable', Rule::exists('levels', 'id')->where('institution_id', $institutionId)],
            'publish_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:publish_at'],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'is_pinned' => ['nullable', 'boolean'],
            'require_acknowledgement' => ['nullable', 'boolean'],
            'attachment' => ['nullable', File::types(['pdf', 'jpg', 'jpeg', 'png', 'webp', 'docx'])->max($maxKb)],
        ]);

        if ($data['audience_type'] === 'class' && empty($data['class_id'])) {
            return back()->withErrors(['class_id' => 'Pilih kelas penerima.'])->withInput();
        }
        if ($data['audience_type'] === 'group' && empty($data['learning_group_id'])) {
            return back()->withErrors(['learning_group_id' => 'Pilih kelompok penerima.'])->withInput();
        }
        if ($data['audience_type'] === 'level' && empty($data['level_id'])) {
            return back()->withErrors(['level_id' => 'Pilih jenjang penerima.'])->withInput();
        }

        $asset = $request->hasFile('attachment')
            ? $this->media->store($request->file('attachment'), $request->user(), 'announcements', 'restricted', (int) config('sullam.media_retention_days', 180))
            : null;

        try {
            $announcement = DB::transaction(function () use ($request, $data, $institutionId, $asset): Announcement {
                $announcement = Announcement::create([
                    ...collect($data)->except(['attachment', 'level_id'])->all(),
                    'institution_id' => $institutionId,
                    'created_by_user_id' => $request->user()->id,
                    'class_id' => $data['audience_type'] === 'class' ? ($data['class_id'] ?? null) : null,
                    'learning_group_id' => $data['audience_type'] === 'group' ? ($data['learning_group_id'] ?? null) : null,
                    'is_pinned' => (bool) ($data['is_pinned'] ?? false),
                    'require_acknowledgement' => (bool) ($data['require_acknowledgement'] ?? false),
                    'publish_at' => $data['publish_at'] ?: now(),
                    'attachment_media_id' => $asset?->id,
                    'attachment_original_name' => $asset?->original_name,
                ]);

                $targetId = match ($data['audience_type']) {
                    'class' => (int) $data['class_id'],
                    'group' => (int) $data['learning_group_id'],
                    'level' => (int) $data['level_id'],
                    default => null,
                };
                $announcement->targets()->create([
                    'target_type' => $data['audience_type'],
                    'target_id' => $targetId,
                ]);

                if ($asset) {
                    $this->media->link($asset, $announcement, 'attachment');
                }

                return $announcement;
            });
        } catch (\Throwable $exception) {
            if ($asset instanceof MediaAsset) {
                $this->media->delete($asset);
            }
            throw $exception;
        }

        return back()->with('success', 'Pengumuman berhasil disimpan.');
    }

    public function storeFriday(Request $request): RedirectResponse
    {
        $institutionId = $request->user()->institution_id;
        $maxKb = (int) config('sullam.upload_max_kb', 25600);
        $data = $request->validate([
            'target_type' => ['nullable', Rule::in(['all', 'class', 'group', 'level'])],
            'class_id' => ['nullable', Rule::exists('classes', 'id')->where('institution_id', $institutionId)],
            'learning_group_id' => ['nullable', Rule::exists('learning_groups', 'id')->where('institution_id', $institutionId)],
            'level_id' => ['nullable', Rule::exists('levels', 'id')->where('institution_id', $institutionId)],
            'session_date' => ['required', 'date'],
            'category' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:190'],
            'objectives' => ['nullable', 'string', 'max:5000'],
            'summary' => ['required', 'string', 'max:20000'],
            'quran_surah_id' => ['nullable', 'exists:quran_surahs,id'],
            'quran_start_verse' => ['nullable', 'integer', 'min:1'],
            'quran_end_verse' => ['nullable', 'integer', 'gte:quran_start_verse'],
            'home_follow_up' => ['nullable', 'string', 'max:5000'],
            'media_url' => ['nullable', 'url', 'max:500'],
            'worksheet' => ['nullable', File::types(['pdf', 'jpg', 'jpeg', 'png', 'webp', 'docx'])->max($maxKb)],
            'family_response_enabled' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['draft', 'published'])],
        ]);

        $targetType = $data['target_type'] ?? (empty($data['class_id']) ? 'all' : 'class');
        $targetField = match ($targetType) {
            'class' => 'class_id',
            'group' => 'learning_group_id',
            'level' => 'level_id',
            default => null,
        };
        if ($targetField && empty($data[$targetField])) {
            return back()->withErrors([$targetField => 'Pilih sasaran Pembinaan Jumat.'])->withInput();
        }

        if (! empty($data['quran_surah_id']) && ! empty($data['quran_end_verse'])) {
            $surah = QuranSurah::findOrFail($data['quran_surah_id']);
            abort_if($data['quran_end_verse'] > $surah->verse_count, 422, 'Rentang ayat melebihi jumlah ayat surah.');
        }

        $asset = $request->hasFile('worksheet')
            ? $this->media->store($request->file('worksheet'), $request->user(), 'friday', 'restricted', (int) config('sullam.media_retention_days', 180))
            : null;
        $year = AcademicYear::where('institution_id', $institutionId)->where('is_active', true)->firstOrFail();

        try {
            DB::transaction(function () use ($request, $data, $institutionId, $targetType, $asset, $year): void {
                $session = FridayDevelopmentSession::create([
                    ...collect($data)->except(['worksheet', 'target_type', 'learning_group_id', 'level_id'])->all(),
                    'class_id' => $targetType === 'class' ? ($data['class_id'] ?? null) : null,
                    'institution_id' => $institutionId,
                    'academic_year_id' => $year->id,
                    'created_by_user_id' => $request->user()->id,
                    'worksheet_media_id' => $asset?->id,
                    'worksheet_original_name' => $asset?->original_name,
                    'family_response_enabled' => (bool) ($data['family_response_enabled'] ?? false),
                    'published_at' => $data['status'] === 'published' ? now() : null,
                ]);

                $session->targets()->create([
                    'class_id' => $targetType === 'class' ? $data['class_id'] : null,
                    'learning_group_id' => $targetType === 'group' ? $data['learning_group_id'] : null,
                    'level_id' => $targetType === 'level' ? $data['level_id'] : null,
                    'target_all' => $targetType === 'all',
                ]);

                if ($asset) {
                    $this->media->link($asset, $session, 'worksheet');
                }
            });
        } catch (\Throwable $exception) {
            if ($asset instanceof MediaAsset) {
                $this->media->delete($asset);
            }
            throw $exception;
        }

        return back()->with('success', 'Pembinaan Jumat berhasil disimpan.');
    }
}
