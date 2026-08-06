<?php

namespace App\Http\Controllers;

use App\Models\AssignmentSubmission;
use App\Models\LiaisonMessage;
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
        return $this->localFile($submission->file_path,$submission->mime_type,$submission->original_name ?: 'bukti-tugas');
    }

    public function liaison(Request $request,LiaisonMessage $message): BinaryFileResponse
    {
        $thread=$message->thread;
        abort_unless($thread && $thread->institution_id===$request->user()->institution_id,404);
        $allowed=$request->user()->hasAnyRole(['institution_admin','head','superadmin']);
        if($request->user()->hasRole('teacher')) $allowed=$thread->assigned_teacher_id===$request->user()->teacher?->id;
        if($request->user()->hasRole('guardian')) $allowed=$request->user()->guardian?->students()->whereKey($thread->student_id)->exists()??false;
        abort_unless($allowed,403);
        return $this->localFile($message->file_path,$message->mime_type,$message->original_name ?: 'lampiran');
    }

    private function localFile(?string $path,?string $mime,string $name): BinaryFileResponse
    {
        abort_unless($path && Storage::disk('local')->exists($path),404);
        $response=response()->file(Storage::disk('local')->path($path),['Content-Type'=>$mime ?: 'application/octet-stream','X-Content-Type-Options'=>'nosniff']);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE,$name);
        return $response;
    }
}
