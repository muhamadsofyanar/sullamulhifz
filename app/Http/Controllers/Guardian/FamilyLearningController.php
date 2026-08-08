<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\FamilyLearningActivity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FamilyLearningController extends Controller
{
    public function index(Request $request): View
    {
        $guardian = $request->user()->guardian;
        abort_unless($guardian, 403, 'Profil wali belum terhubung.');
        $studentIds = $guardian->students()->pluck('students.id');
        $activities = FamilyLearningActivity::query()->with(['student','lesson.module.program','creator'])
            ->where('institution_id',$request->user()->institution_id)->whereIn('student_id',$studentIds)
            ->latest()->get();
        return view('guardian.family-learning.index', compact('activities'));
    }

    public function complete(Request $request, FamilyLearningActivity $activity): RedirectResponse
    {
        $guardian = $request->user()->guardian;
        abort_unless($guardian, 403);
        abort_unless((int) $activity->institution_id === (int) $request->user()->institution_id, 404);
        abort_unless($guardian->students()->where('students.id',$activity->student_id)->exists(), 403);
        abort_unless(in_array($activity->status,['assigned','in_progress'],true), 422, 'Aktivitas ini sudah ditutup.');
        $data = $request->validate(['guardian_reflection'=>['required','string','max:10000']]);
        $activity->update([
            'guardian_reflection'=>$data['guardian_reflection'],
            'completed_by_user_id'=>$request->user()->id,
            'completed_at'=>now(),
            'status'=>'completed',
        ]);
        return back()->with('success','Aktivitas selesai. Refleksi Anda sudah tersimpan untuk guru.');
    }
}
