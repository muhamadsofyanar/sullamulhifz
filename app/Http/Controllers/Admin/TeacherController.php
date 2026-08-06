<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(Request $request): View
    {
        $teachers = Teacher::with(['user','assignments.schoolClass','assignments.learningGroup'])
            ->where('institution_id',$request->user()->institution_id)
            ->orderBy('full_name')->paginate(20);
        return view('admin.teachers.index', compact('teachers'));
    }

    public function create(): View
    {
        return view('admin.teachers.form', ['teacher'=>new Teacher(['status'=>'active'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $institutionId = $request->user()->institution_id;
        $data = $request->validate([
            'full_name'=>['required','string','max:190'],
            'nickname'=>['nullable','string','max:100'],
            'employee_code'=>['nullable','string','max:50', Rule::unique('teachers')->where('institution_id',$institutionId)],
            'gender'=>['nullable', Rule::in(['male','female'])],
            'phone'=>['required','string','max:30','unique:users,phone'],
            'email'=>['nullable','email','max:190','unique:users,email'],
            'password'=>['required','string','min:10'],
            'specialization'=>['nullable','string','max:190'],
            'joined_at'=>['nullable','date'],
            'address'=>['nullable','string'],
        ]);

        $teacher = DB::transaction(function () use ($data, $institutionId): Teacher {
            $user = User::create(['institution_id'=>$institutionId,'name'=>$data['full_name'],'email'=>$data['email'],'phone'=>$data['phone'],'password'=>Hash::make($data['password']),'status'=>'active','must_change_password'=>true]);
            $role = Role::where('name','teacher')->firstOrFail();
            $user->roles()->attach($role->id,['institution_id'=>$institutionId,'status'=>'active']);
            $teacherData=$data;
            unset($teacherData['password']);
            return Teacher::create([...$teacherData,'institution_id'=>$institutionId,'user_id'=>$user->id,'status'=>'active']);
        });

        return redirect()->route('admin.teachers.index')->with('success', 'Akun guru '.$teacher->full_name.' berhasil dibuat.');
    }
}
