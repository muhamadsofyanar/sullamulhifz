<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\InfaqEvidence;
use App\Models\MediaAsset;
use App\Models\InfaqTransaction;
use App\Support\Feature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class InfaqEvidenceController extends Controller
{
    public function original(Request $request, InfaqEvidence $evidence): BinaryFileResponse
    {
        $evidence->load(['realisation', 'originalAsset']);
        $allowed = $request->user()->hasRole('superadmin') || ($request->user()->hasPermission('infaq.audit.view') && (int) $evidence->institution_id === (int) $request->user()->institution_id);
        abort_unless($allowed && $evidence->originalAsset, 404);
        ActivityLog::create([
            'institution_id' => $evidence->institution_id, 'user_id' => $request->user()->id,
            'action' => 'infaq.evidence_original_viewed', 'subject_type' => $evidence::class,
            'subject_id' => $evidence->id, 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent(), 'created_at' => now(),
        ]);

        return $this->file($evidence->originalAsset);
    }

    public function transferProof(Request $request, InfaqTransaction $transaction): BinaryFileResponse
    {
        $transaction->load('transferProof');
        $allowed = $request->user()->hasRole('superadmin') || ($request->user()->hasAnyPermission(['infaq.verify', 'infaq.audit.view']) && (int) $transaction->institution_id === (int) $request->user()->institution_id);
        abort_unless($allowed && $transaction->transferProof, 404);
        ActivityLog::create([
            'institution_id' => $transaction->institution_id, 'user_id' => $request->user()->id,
            'action' => 'infaq.transfer_proof_viewed', 'subject_type' => $transaction::class,
            'subject_id' => $transaction->id, 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent(), 'created_at' => now(),
        ]);

        return $this->file($transaction->transferProof);
    }

    public function redacted(InfaqEvidence $evidence): BinaryFileResponse
    {
        $evidence->load(['realisation', 'publicAsset']);
        abort_unless(
            Feature::enabled('v610_pilot', $evidence->institution_id, false)
            && $evidence->realisation?->status === 'verified'
            && $evidence->public_review_status === 'approved'
            && $evidence->publicAsset,
            404,
        );

        return $this->file($evidence->publicAsset);
    }

    private function file(MediaAsset $asset): BinaryFileResponse
    {
        abort_unless($asset->processing_status === 'ready' && Storage::disk($asset->disk)->exists($asset->storagePath()), 404);
        $name = Str::of($asset->original_name)->replaceMatches('/[\r\n\\\/]+/', '-')->limit(180, '')->toString() ?: 'bukti-infak';
        $response = response()->file(Storage::disk($asset->disk)->path($asset->storagePath()), [
            'Content-Type' => $asset->mime_type ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store, max-age=0',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self' data:; style-src 'unsafe-inline'; sandbox",
        ]);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $name);

        return $response;
    }
}
