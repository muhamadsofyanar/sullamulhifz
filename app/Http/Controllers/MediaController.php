<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\AssignmentSubmission;
use App\Models\FridayDevelopmentSession;
use App\Models\LiaisonMessage;
use App\Models\MediaAsset;
use App\Services\ContentAudienceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class MediaController extends Controller
{
    public function __construct(private readonly ContentAudienceService $audience)
    {
    }

    public function submission(Request $request, AssignmentSubmission $submission): BinaryFileResponse
    {
        $submission->load(['recipient.assignment', 'mediaAsset']);
        $assignment = $submission->recipient?->assignment;
        abort_unless($assignment, 404);

        $user = $request->user();
        $allowed = $this->isSystemSuperadmin($user)
            || ($user->hasAnyRole(['institution_admin', 'head']) && (int) $assignment->institution_id === (int) $user->institution_id)
            || ($user->hasRole('teacher') && (int) $assignment->created_by_teacher_id === (int) $user->teacher?->id)
            || ($user->hasRole('guardian') && ($user->guardian?->students()->whereKey($submission->recipient->student_id)->exists() ?? false));

        abort_unless($allowed, 403);
        $this->auditPrivilegedView($request, $submission, (int) $assignment->institution_id);

        if ($submission->mediaAsset) {
            return $this->assetFile($submission->mediaAsset);
        }

        return $this->storedFile('local', $submission->file_path, $submission->mime_type, $submission->original_name ?: 'bukti-tugas');
    }

    public function liaison(Request $request, LiaisonMessage $message): BinaryFileResponse
    {
        $message->load(['thread', 'mediaAsset']);
        $thread = $message->thread;
        abort_unless($thread, 404);

        $user = $request->user();
        abort_unless($this->isSystemSuperadmin($user) || (int) $thread->institution_id === (int) $user->institution_id, 404);

        $allowed = $this->isSystemSuperadmin($user)
            || $user->hasAnyRole(['institution_admin', 'head'])
            || ($user->hasRole('teacher') && (int) $thread->assigned_teacher_id === (int) $user->teacher?->id)
            || ($user->hasRole('guardian') && ($user->guardian?->students()->whereKey($thread->student_id)->exists() ?? false));

        abort_unless($allowed, 403);
        $this->auditPrivilegedView($request, $message, (int) $thread->institution_id);

        if ($message->mediaAsset) {
            return $this->assetFile($message->mediaAsset);
        }

        return $this->storedFile('local', $message->file_path, $message->mime_type, $message->original_name ?: 'lampiran');
    }

    public function announcement(Request $request, Announcement $announcement): BinaryFileResponse
    {
        $announcement->load(['targets', 'attachmentMedia']);
        abort_unless($this->audience->announcementVisibleTo($announcement, $request->user()), 403);

        if ($announcement->attachmentMedia) {
            return $this->assetFile($announcement->attachmentMedia);
        }

        return $this->storedFile('public', $announcement->attachment_path, null, $announcement->attachment_original_name ?: 'lampiran-pengumuman');
    }

    public function friday(Request $request, FridayDevelopmentSession $session): BinaryFileResponse
    {
        $session->load(['targets', 'worksheetMedia']);
        abort_unless($this->audience->fridayVisibleTo($session, $request->user()), 403);

        if ($session->worksheetMedia) {
            return $this->assetFile($session->worksheetMedia);
        }

        return $this->storedFile('public', $session->worksheet_path, null, $session->worksheet_original_name ?: 'lembar-aktivitas');
    }

    public function adminAsset(Request $request, MediaAsset $mediaAsset): BinaryFileResponse
    {
        $user = $request->user();
        $allowed = $this->isSystemSuperadmin($user)
            || ((int) $mediaAsset->institution_id === (int) $user->institution_id
                && ($user->hasAnyRole(['institution_admin', 'head']) || (int) $mediaAsset->uploaded_by_user_id === (int) $user->id));

        abort_unless($allowed, 403);
        $this->auditPrivilegedView($request, $mediaAsset, (int) $mediaAsset->institution_id);

        return $this->assetFile($mediaAsset);
    }

    private function assetFile(MediaAsset $asset): BinaryFileResponse
    {
        abort_unless($asset->processing_status === 'ready', 404);

        return $this->storedFile($asset->disk, $asset->storagePath(), $asset->mime_type, $asset->original_name);
    }

    private function storedFile(string $disk, ?string $path, ?string $mime, string $name): BinaryFileResponse
    {
        abort_unless($path && Storage::disk($disk)->exists($path), 404);

        $safeName = Str::of($name)->replaceMatches('/[\r\n\\\/]+/', '-')->limit(180, '')->toString();
        $mime = $mime ?: (Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream');
        $inline = str_starts_with($mime, 'image/')
            || str_starts_with($mime, 'audio/')
            || str_starts_with($mime, 'video/')
            || $mime === 'application/pdf';

        $response = response()->file(Storage::disk($disk)->path($path), [
            'Content-Type' => $mime,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0, must-revalidate',
            'Content-Security-Policy' => "default-src 'none'; media-src 'self'; img-src 'self' data:; style-src 'unsafe-inline'; sandbox",
        ]);
        $response->setContentDisposition(
            $inline ? ResponseHeaderBag::DISPOSITION_INLINE : ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $safeName ?: 'file',
        );

        return $response;
    }

    private function auditPrivilegedView(Request $request, object $subject, int $institutionId): void
    {
        if (! $request->user()->hasAnyRole(['superadmin', 'institution_admin', 'head'])) {
            return;
        }

        ActivityLog::create([
            'institution_id' => $institutionId,
            'user_id' => $request->user()->id,
            'action' => 'view_private',
            'subject_type' => $subject::class,
            'subject_id' => method_exists($subject, 'getKey') ? $subject->getKey() : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }

    private function isSystemSuperadmin($user): bool
    {
        return $user->hasRole('superadmin') && $user->institution_id === null;
    }
}
