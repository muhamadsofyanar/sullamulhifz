<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\FridayDevelopmentSession;
use App\Models\SchoolClass;
use App\Services\ContentAudienceService;
use App\Support\StudentPledge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ContentFeedController extends Controller
{
    public function __construct(private readonly ContentAudienceService $audience)
    {
    }

    public function announcements(Request $request): View
    {
        $user = $request->user();
        $classIds = $this->audience->classIds($user);
        $groupIds = $this->audience->groupIds($user);
        $levelIds = SchoolClass::whereIn('id', $classIds)->pluck('level_id');
        $roleAudiences = collect([
            $user->hasAnyRole(['superadmin', 'institution_admin', 'head']) ? 'admins' : null,
            $user->hasRole('teacher') ? 'teachers' : null,
            $user->hasRole('guardian') ? 'guardians' : null,
        ])->filter()->values();
        $roleIds = $user->roles()->wherePivot('status', 'active')->pluck('roles.id');

        $items = Announcement::with([
                'schoolClass', 'learningGroup', 'targets', 'attachmentMedia',
                'reads' => fn ($q) => $q->where('user_id', $user->id),
            ])
            ->where('institution_id', $user->institution_id)
            ->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('publish_at')->orWhere('publish_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->where(function ($query) use ($classIds, $groupIds, $levelIds, $roleAudiences, $roleIds, $user): void {
                $legacyAudience = function ($legacy) use ($classIds, $groupIds, $roleAudiences): void {
                    $legacy->where('audience_type', 'all')
                        ->orWhereIn('audience_type', $roleAudiences)
                        ->orWhere(fn ($q) => $q->where('audience_type', 'class')->whereIn('class_id', $classIds))
                        ->orWhere(fn ($q) => $q->where('audience_type', 'group')->whereIn('learning_group_id', $groupIds));
                };

                if (! Schema::hasTable('announcement_targets')) {
                    $query->where($legacyAudience);
                    return;
                }

                // Pengumuman lama tanpa target tetap memakai kolom audience_type.
                // Ketika target sudah tersedia, target menjadi sumber kebenaran agar
                // pengumuman jenjang/kelas tidak bocor karena nilai legacy yang lebih luas.
                $query->where(function ($legacy) use ($legacyAudience): void {
                    $legacy->whereDoesntHave('targets')->where($legacyAudience);
                })->orWhereHas('targets', function ($targets) use ($classIds, $groupIds, $levelIds, $roleAudiences, $roleIds, $user): void {
                    $targets->whereIn('target_type', collect(['all', 'institution'])->merge($roleAudiences)->all())
                        ->orWhere(fn ($q) => $q->where('target_type', 'class')->whereIn('target_id', $classIds))
                        ->orWhere(fn ($q) => $q->where('target_type', 'group')->whereIn('target_id', $groupIds))
                        ->orWhere(fn ($q) => $q->where('target_type', 'level')->whereIn('target_id', $levelIds))
                        ->orWhere(fn ($q) => $q->where('target_type', 'role')->whereIn('target_id', $roleIds))
                        ->orWhere(fn ($q) => $q->where('target_type', 'user')->where('target_id', $user->id));
                });
            })
            ->orderByDesc('is_pinned')
            ->latest('publish_at')
            ->paginate(20);

        foreach ($items as $item) {
            AnnouncementRead::firstOrCreate(
                ['announcement_id' => $item->id, 'user_id' => $user->id],
                ['read_at' => now()],
            );
        }

        return view('content.announcements', compact('items'));
    }

    public function acknowledge(Request $request, Announcement $announcement): RedirectResponse
    {
        abort_unless(Schema::hasTable('announcement_reads'), 503, 'Fitur konfirmasi sedang disiapkan.');
        $announcement->load('targets');
        abort_unless($this->audience->announcementVisibleTo($announcement, $request->user()), 404);

        AnnouncementRead::updateOrCreate(
            ['announcement_id' => $announcement->id, 'user_id' => $request->user()->id],
            ['read_at' => now(), 'acknowledged_at' => now()],
        );

        return back()->with('success', 'Pengumuman telah dikonfirmasi dibaca.');
    }

    public function friday(Request $request): View
    {
        $user = $request->user();
        $classIds = $this->audience->classIds($user);
        $groupIds = $this->audience->groupIds($user);
        $levelIds = SchoolClass::whereIn('id', $classIds)->pluck('level_id');

        $items = FridayDevelopmentSession::with([
                'schoolClass', 'surah', 'targets.schoolClass', 'targets.learningGroup', 'targets.level', 'worksheetMedia',
            ])
            ->where('institution_id', $user->institution_id)
            ->where('status', 'published')
            ->where(function ($query) use ($classIds, $groupIds, $levelIds): void {
                if (Schema::hasTable('friday_session_targets')) {
                    $query->where(function ($legacy) use ($classIds): void {
                        $legacy->whereDoesntHave('targets')
                            ->where(fn ($scope) => $scope->whereNull('class_id')->orWhereIn('class_id', $classIds));
                    })->orWhereHas('targets', function ($targets) use ($classIds, $groupIds, $levelIds): void {
                        $targets->where('target_all', true)
                            ->orWhereIn('class_id', $classIds)
                            ->orWhereIn('learning_group_id', $groupIds)
                            ->orWhereIn('level_id', $levelIds);
                    });

                    return;
                }

                $query->whereNull('class_id')->orWhereIn('class_id', $classIds);
            })
            ->latest('session_date')
            ->paginate(20);

        return view('content.friday', compact('items'));
    }

    public function pledge(Request $request): View
    {
        return view('content.pledge', [
            'pledge' => StudentPledge::forInstitution($request->user()->institution_id),
        ]);
    }
}
