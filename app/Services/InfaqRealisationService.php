<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\InfaqEvidence;
use App\Models\InfaqRealisation;
use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InfaqRealisationService
{
    public function __construct(private readonly InfaqLedgerService $ledger) {}

    /** @param array<string,mixed> $data */
    public function create(User $actor, array $data, MediaAsset $original, MediaAsset $public): InfaqRealisation
    {
        abort_unless($actor->institution_id && (int) $original->institution_id === (int) $actor->institution_id && (int) $public->institution_id === (int) $actor->institution_id, 404);

        return DB::transaction(function () use ($actor, $data, $original, $public): InfaqRealisation {
            $realisation = InfaqRealisation::create([
                'public_id' => (string) Str::uuid(), 'institution_id' => $actor->institution_id,
                'category' => $data['category'], 'program_name' => $data['program_name'],
                'purpose' => $data['purpose'], 'amount' => $data['amount'],
                'beneficiary_count' => $data['beneficiary_count'] ?? 0,
                'impact_summary' => $data['impact_summary'] ?? null,
                'realised_on' => $data['realised_on'], 'status' => 'submitted',
                'created_by_user_id' => $actor->id, 'submitted_at' => now(),
            ]);
            InfaqEvidence::create([
                'institution_id' => $actor->institution_id, 'realisation_id' => $realisation->id,
                'evidence_type' => $data['evidence_type'], 'original_media_asset_id' => $original->id,
                'public_media_asset_id' => $public->id, 'public_review_status' => 'pending',
            ]);
            $this->audit($actor, 'infaq.realisation_submitted', $realisation, null, $realisation->toArray());

            return $realisation->load('evidences');
        }, 3);
    }

    public function review(InfaqRealisation $realisation, User $reviewer, string $decision, string $note): InfaqRealisation
    {
        return DB::transaction(function () use ($realisation, $reviewer, $decision, $note): InfaqRealisation {
            $locked = InfaqRealisation::query()->with('evidences')->lockForUpdate()->findOrFail($realisation->id);
            abort_unless($reviewer->hasRole('superadmin') || (int) $locked->institution_id === (int) $reviewer->institution_id, 404);
            abort_if((int) $locked->created_by_user_id === (int) $reviewer->id, 422, 'Pencatat realisasi tidak boleh memverifikasi catatannya sendiri.');
            abort_unless($locked->status === 'submitted', 422, 'Hanya realisasi yang diajukan yang dapat diperiksa.');
            if ($decision === 'verified' && ($locked->evidences->isEmpty() || $locked->evidences->contains(fn ($evidence) => ! $evidence->original_media_asset_id || ! $evidence->public_media_asset_id))) {
                throw ValidationException::withMessages(['evidence' => 'Bukti asli dan versi publik tersamarkan wajib tersedia.']);
            }
            $old = $locked->toArray();
            if ($decision === 'verified') {
                $this->ledger->debitRealisation($locked, $reviewer);
            }
            $locked->update([
                'status' => $decision, 'reviewed_by_user_id' => $reviewer->id,
                'reviewed_at' => now(), 'review_note' => $note,
            ]);
            $locked->evidences()->update([
                'public_review_status' => $decision === 'verified' ? 'approved' : 'rejected',
                'public_reviewed_by_user_id' => $reviewer->id, 'public_reviewed_at' => now(),
                'review_note' => $note,
            ]);
            $this->audit($reviewer, 'infaq.realisation_'.$decision, $locked, $old, $locked->fresh()->toArray(), $note);

            return $locked->refresh()->load('evidences');
        }, 3);
    }

    /** @param array<string,mixed> $data */
    public function resubmit(InfaqRealisation $realisation, User $actor, array $data, ?MediaAsset $original = null, ?MediaAsset $public = null): InfaqRealisation
    {
        return DB::transaction(function () use ($realisation, $actor, $data, $original, $public): InfaqRealisation {
            $locked = InfaqRealisation::query()->with('evidences')->lockForUpdate()->findOrFail($realisation->id);
            abort_unless((int) $locked->institution_id === (int) $actor->institution_id && $locked->status === 'rejected', 404);
            $old = $locked->toArray();
            $locked->update([
                'purpose' => $data['purpose'], 'impact_summary' => $data['impact_summary'] ?? null,
                'status' => 'submitted', 'submitted_at' => now(), 'reviewed_by_user_id' => null,
                'reviewed_at' => null, 'review_note' => null,
            ]);
            $evidence = $locked->evidences->first();
            abort_unless($evidence, 422, 'Bukti realisasi tidak ditemukan.');
            $evidence->update([
                'original_media_asset_id' => $original?->id ?: $evidence->original_media_asset_id,
                'public_media_asset_id' => $public?->id ?: $evidence->public_media_asset_id,
                'public_review_status' => 'pending', 'public_reviewed_by_user_id' => null,
                'public_reviewed_at' => null, 'review_note' => null,
            ]);
            $this->audit($actor, 'infaq.realisation_resubmitted', $locked, $old, $locked->fresh()->toArray(), 'Perbaikan setelah penolakan.');

            return $locked->refresh()->load('evidences');
        }, 3);
    }

    private function audit(User $actor, string $action, object $subject, ?array $old, array $new, ?string $reason = null): void
    {
        ActivityLog::create([
            'institution_id' => $actor->institution_id, 'user_id' => $actor->id, 'action' => $action,
            'subject_type' => $subject::class, 'subject_id' => $subject->getKey(),
            'old_values' => $old, 'new_values' => $new, 'reason' => $reason, 'created_at' => now(),
        ]);
    }
}
