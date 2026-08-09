<?php

namespace App\Services;

use App\Models\CommunityModerationAction;
use App\Models\CommunityPost;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CommunityModerationService
{
    public function moderate(CommunityPost $post, User $moderator, string $action, ?string $reason, string $policyVersion): CommunityModerationAction
    {
        $post->loadMissing('space');
        abort_unless((int) $post->space->institution_id === (int) $moderator->institution_id, 403);
        if (! in_array($action, ['approve', 'reject', 'hide'], true)) {
            throw ValidationException::withMessages(['action' => 'Aksi moderasi tidak dikenal.']);
        }

        return DB::transaction(function () use ($post, $moderator, $action, $reason, $policyVersion): CommunityModerationAction {
            $post->update(match ($action) {
                'approve' => ['status' => 'published', 'published_at' => $post->published_at ?? now()],
                'reject' => ['status' => 'rejected', 'published_at' => null],
                'hide' => ['status' => 'hidden'],
            });

            return CommunityModerationAction::create([
                'institution_id' => $post->space->institution_id,
                'community_space_id' => $post->community_space_id,
                'community_post_id' => $post->id,
                'moderator_user_id' => $moderator->id,
                'action' => $action,
                'reason' => $reason,
                'policy_version' => $policyVersion,
            ]);
        });
    }
}
