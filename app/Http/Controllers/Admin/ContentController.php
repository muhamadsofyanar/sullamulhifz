<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\FridayDevelopmentSession;
use App\Models\QuranSurah;
use App\Models\SchoolClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContentController extends Controller
{
    public function index(Request $request): View
    {
        $institutionId=$request->user()->institution_id;
        return view('admin.content.index',[
            'announcements'=>Announcement::with('schoolClass')->where('institution_id',$institutionId)->latest()->paginate(10,'*','ann_page'),
            'fridaySessions'=>FridayDevelopmentSession::with(['schoolClass','surah'])->where('institution_id',$institutionId)->latest('session_date')->paginate(10,'*','friday_page'),
            'classes'=>SchoolClass::where('institution_id',$institutionId)->where('status','active')->orderBy('name')->get(),
            'surahs'=>QuranSurah::orderBy('id')->get(),
        ]);
    }

    public function storeAnnouncement(Request $request): RedirectResponse
    {
        $data=$request->validate([
            'title'=>['required','string','max:190'],
            'content'=>['required','string'],
            'class_id'=>['nullable','exists:classes,id'],
            'publish_at'=>['nullable','date'],
            'expires_at'=>['nullable','date','after:publish_at'],
            'status'=>['required',Rule::in(['draft','published'])],
        ]);
        if (! empty($data['class_id'])) {
            abort_unless(SchoolClass::where('institution_id',$request->user()->institution_id)->whereKey($data['class_id'])->exists(),403);
        }
        Announcement::create([...$data,'institution_id'=>$request->user()->institution_id,'created_by_user_id'=>$request->user()->id,'publish_at'=>$data['publish_at']?:now()]);
        return back()->with('success','Pengumuman berhasil disimpan.');
    }

    public function storeFriday(Request $request): RedirectResponse
    {
        $data=$request->validate([
            'class_id'=>['nullable','exists:classes,id'],
            'session_date'=>['required','date'],
            'category'=>['required','string','max:100'],
            'title'=>['required','string','max:190'],
            'objectives'=>['nullable','string'],
            'summary'=>['required','string'],
            'quran_surah_id'=>['nullable','exists:quran_surahs,id'],
            'quran_start_verse'=>['nullable','integer','min:1'],
            'quran_end_verse'=>['nullable','integer','gte:quran_start_verse'],
            'home_follow_up'=>['nullable','string'],
            'status'=>['required',Rule::in(['draft','published'])],
        ]);
        if (! empty($data['class_id'])) {
            abort_unless(SchoolClass::where('institution_id',$request->user()->institution_id)->whereKey($data['class_id'])->exists(),403);
        }
        if (! empty($data['quran_surah_id']) && ! empty($data['quran_end_verse'])) {
            $surah=QuranSurah::findOrFail($data['quran_surah_id']);
            abort_if($data['quran_end_verse']>$surah->verse_count,422,'Rentang ayat melebihi jumlah ayat surah.');
        }
        $year=AcademicYear::where('institution_id',$request->user()->institution_id)->where('is_active',true)->firstOrFail();
        FridayDevelopmentSession::create([...$data,'institution_id'=>$request->user()->institution_id,'academic_year_id'=>$year->id,'created_by_user_id'=>$request->user()->id,'published_at'=>$data['status']==='published'?now():null]);
        return back()->with('success','Pembinaan Jumat berhasil disimpan.');
    }
}
