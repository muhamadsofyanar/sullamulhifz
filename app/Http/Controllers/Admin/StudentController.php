<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\ClassEnrollment;
use App\Models\Guardian;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\MediaAsset;
use App\Services\MediaStorageService;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(private readonly MediaStorageService $media)
    {
    }

    public function index(Request $request): View
    {
        $students = Student::with(['currentEnrollment.schoolClass', 'guardians'])
            ->where('institution_id', $request->user()->institution_id)
            ->when($request->filled('q'), fn ($q) => $q->where('full_name', 'like', '%'.$request->string('q').'%'))
            ->orderBy('full_name')->paginate(20)->withQueryString();

        return view('admin.students.index', compact('students'));
    }

    public function create(Request $request): View
    {
        return view('admin.students.form', [
            'student' => new Student(['status'=>'active','stifin_status'=>'untested']),
            'classes' => $this->classes($request),
            'currentClassId' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateStudent($request);
        $institutionId = $request->user()->institution_id;
        $photoAsset = $request->hasFile('photo')
            ? $this->media->store($request->file('photo'), $request->user(), 'student-photos', 'restricted')
            : null;

        try {
        $student = DB::transaction(function () use ($request, $data, $institutionId, $photoAsset): Student {
            $studentData = $data;
            unset($studentData['class_id'], $studentData['photo']);
            $student = Student::create([
                ...$studentData,
                'institution_id' => $institutionId,
                'student_code' => $data['student_code'] ?: null,
                'photo_media_id' => $photoAsset?->id,
            ]);
            if ($photoAsset) {
                $this->media->link($photoAsset, $student, 'avatar');
            }

            if (blank($student->student_code)) {
                $student->update(['student_code' => 'SAN-'.str_pad((string) $student->id, 5, '0', STR_PAD_LEFT)]);
            }

            $class = SchoolClass::where('institution_id',$institutionId)->findOrFail($request->integer('class_id'));
            $student->update(['branch_id' => $class->branch_id]);
            ClassEnrollment::create(['class_id'=>$class->id,'student_id'=>$student->id,'academic_year_id'=>$class->academic_year_id,'enrolled_at'=>now()->toDateString(),'status'=>'active']);

            if ($request->filled('guardian_name')) {
                $this->attachGuardian($student, $request, $institutionId);
            }

            return $student;
        });
        } catch (\Throwable $exception) {
            if ($photoAsset instanceof MediaAsset) {
                $this->media->delete($photoAsset);
            }
            throw $exception;
        }

        return redirect()->route('admin.students.show', $student)->with('success', 'Data santri berhasil disimpan.');
    }

    public function show(Request $request, Student $student): View
    {
        $this->guardInstitution($request, $student);
        $student->load(['guardians.user','currentEnrollment.schoolClass','tahsinRecords.surah','memorizationRecords.surah','memorizationTargets.rubu','memorizationTargets.surah','memorizationTargets.marhalah','learningObservations.teacher','murajaahRecords.surah']);
        return view('admin.students.show', compact('student'));
    }

    public function edit(Request $request, Student $student): View
    {
        $this->guardInstitution($request, $student);
        return view('admin.students.form', [
            'student'=>$student,
            'classes'=>$this->classes($request),
            'currentClassId'=>$student->currentEnrollment?->class_id,
        ]);
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $this->guardInstitution($request, $student);
        $data = $this->validateStudent($request, $student);
        $photoAsset = $request->hasFile('photo')
            ? $this->media->store($request->file('photo'), $request->user(), 'student-photos', 'restricted')
            : null;
        $oldPhoto = $student->photoMedia;

        try {
        DB::transaction(function () use ($request, $student, $data, $photoAsset): void {
            $studentData = $data;
            unset($studentData['class_id'], $studentData['photo']);
            if ($photoAsset) {
                $studentData['photo_media_id'] = $photoAsset->id;
                $studentData['photo_path'] = null;
            }
            $student->update($studentData);
            if ($photoAsset) {
                $this->media->link($photoAsset, $student, 'avatar');
            }
            $newClassId = $request->integer('class_id');
            if ($newClassId && $student->currentEnrollment?->class_id !== $newClassId) {
                $previous = ClassEnrollment::query()
                    ->where('student_id', $student->id)
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->latest('enrolled_at')
                    ->first();
                ClassEnrollment::query()->where('student_id', $student->id)->where('status', 'active')
                    ->update(['status'=>'moved','ended_at'=>now()->toDateString()]);
                $class = SchoolClass::where('institution_id',$student->institution_id)->findOrFail($newClassId);
                $student->update(['branch_id' => $class->branch_id]);
                ClassEnrollment::updateOrCreate(
                    ['class_id'=>$class->id,'student_id'=>$student->id,'academic_year_id'=>$class->academic_year_id],
                    ['enrolled_at'=>now()->toDateString(),'ended_at'=>null,'status'=>'active','previous_enrollment_id'=>$previous?->id]
                );
            }
        });
        } catch (\Throwable $exception) {
            if ($photoAsset instanceof MediaAsset) {
                $this->media->delete($photoAsset);
            }
            throw $exception;
        }
        if ($photoAsset && $oldPhoto && $oldPhoto->id !== $photoAsset->id) {
            $this->media->delete($oldPhoto);
        }

        return redirect()->route('admin.students.show', $student)->with('success', 'Data santri berhasil diperbarui.');
    }


    public function addGuardian(Request $request, Student $student): RedirectResponse
    {
        $this->guardInstitution($request, $student);
        $this->attachGuardian($student, $request, $student->institution_id);

        return back()->with('success', 'Wali berhasil dihubungkan dengan santri.');
    }

    private function validateStudent(Request $request, ?Student $student = null): array
    {
        return $request->validate([
            'student_code' => ['nullable','string','max:50', Rule::unique('students','student_code')->where('institution_id',$request->user()->institution_id)->ignore($student?->id)],
            'full_name' => ['required','string','max:190'],
            'nickname' => ['nullable','string','max:100'],
            'gender' => ['nullable', Rule::in(['male','female'])],
            'birth_place' => ['nullable','string','max:100'],
            'birth_date' => ['nullable','date','before:today'],
            'address' => ['nullable','string'],
            'joined_at' => ['nullable','date'],
            'status' => ['required', Rule::in(['active','leave','moved','graduated','stopped'])],
            'special_needs_notes' => ['nullable','string'],
            'stifin_status' => ['required', Rule::in(['untested','tested'])],
            'stifin_result' => ['nullable','string','max:50'],
            'photo' => ['nullable','image','max:3072'],
            'class_id' => ['required','integer', Rule::exists('classes','id')->where('institution_id',$request->user()->institution_id)],
        ]);
    }

    private function attachGuardian(Student $student, Request $request, int $institutionId): void
    {
        $guardianData = $request->validate([
            'guardian_name'=>['required','string','max:190'],
            'guardian_email'=>['nullable','email','max:190'],
            'guardian_phone'=>['required','string','max:30'],
            'guardian_relationship'=>['required','string','max:30'],
            'guardian_password'=>['nullable', PasswordRule::min(12)->letters()->mixedCase()->numbers()],
        ]);

        $user = User::query()
            ->where(function ($query) use ($guardianData): void {
                $query->where('phone', $guardianData['guardian_phone']);
                if ($guardianData['guardian_email']) {
                    $query->orWhere('email', $guardianData['guardian_email']);
                }
            })
            ->first();

        if ($user && $user->institution_id !== $institutionId) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'guardian_phone' => 'Nomor telepon atau email ini sudah digunakan pada lembaga lain.',
            ]);
        }

        if (! $user) {
            $hasInitialPassword = filled($guardianData['guardian_password'] ?? null);
            $user = User::create([
                'institution_id'=>$institutionId,
                'name'=>$guardianData['guardian_name'],
                'email'=>$guardianData['guardian_email'],
                'phone'=>$guardianData['guardian_phone'],
                'password'=>Hash::make($hasInitialPassword ? $guardianData['guardian_password'] : Str::random(64)),
                'status'=>$hasInitialPassword ? 'active' : 'invited',
                'must_change_password'=>$hasInitialPassword,
            ]);
        }
        $role = Role::where('name','guardian')->firstOrFail();
        $user->roles()->syncWithoutDetaching([$role->id => ['institution_id'=>$institutionId,'status'=>'active']]);

        $guardian = Guardian::updateOrCreate(['user_id'=>$user->id], ['institution_id'=>$institutionId,'full_name'=>$guardianData['guardian_name'],'phone'=>$guardianData['guardian_phone'],'email'=>$guardianData['guardian_email'],'status'=>'active']);
        $student->guardians()->syncWithoutDetaching([$guardian->id => ['relationship'=>$guardianData['guardian_relationship'],'is_primary_contact'=>true,'can_receive_notifications'=>true,'can_submit_assignments'=>true,'can_view_learning_records'=>true]]);
    }

    private function classes(Request $request)
    {
        return SchoolClass::with('level')->where('institution_id',$request->user()->institution_id)->where('status','active')->orderBy('name')->get();
    }

    private function guardInstitution(Request $request, Student $student): void
    {
        abort_unless($student->institution_id === $request->user()->institution_id, 404);
    }
}
