<?php

namespace App\Http\Controllers;

use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class MediaController extends Controller
{
    public function submission(Request $request,AssignmentSubmission $submission): BinaryFileResponse
    {
        $submission->load('recipient.assignment');
        $user=$request->user();
        $allowed=false;
        if($user->hasAnyRole(['institution_admin','head','superadmin']) && $submission->recipient->assignment->institution_id===$user->institution_id) $allowed=true;
        if($user->hasRole('teacher') && $submission->recipient->assignment->created_by_teacher_id===$user->teacher?->id) $allowed=true;
        if($user->hasRole('guardian') && $user->guardian?->students()->whereKey($submission->recipient->student_id)->exists()) $allowed=true;
        abort_unless($allowed,403);
        abort_unless($submission->file_path && Storage::disk('local')->exists($submission->file_path),404);
        $response=response()->file(Storage::disk('local')->path($submission->file_path),[
            'Content-Type'=>$submission->mime_type,
            'X-Content-Type-Options'=>'nosniff',
        ]);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE,$submission->original_name ?: 'bukti-tugas');
        return $response;
    }
}
