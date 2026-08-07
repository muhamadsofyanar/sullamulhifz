<?php

namespace Database\Seeders;

use App\Models\ClassEnrollment;
use App\Models\Guardian;
use App\Models\Institution;
use App\Models\Role;
use App\Models\Program;
use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $institution = Institution::where('code', env('INITIAL_INSTITUTION_CODE', 'ALINSYIRAH'))->firstOrFail();
        $plainPassword = (string) env('DEMO_PASSWORD', '');
        if ($plainPassword === '' || strlen($plainPassword) < 12
            || ! preg_match('/[A-Z]/', $plainPassword)
            || ! preg_match('/[a-z]/', $plainPassword)
            || ! preg_match('/[0-9]/', $plainPassword)) {
            throw new RuntimeException('DEMO_PASSWORD wajib diisi, minimal 12 karakter, serta memuat huruf besar, huruf kecil, dan angka.');
        }
        $password = Hash::make($plainPassword);

        $teacherUser = User::updateOrCreate(['email'=>'guru.demo@sullamulhifz.or.id'], ['institution_id'=>$institution->id,'name'=>'Guru Demo','phone'=>'6281200000001','password'=>$password,'status'=>'active','email_verified_at'=>now()]);
        $teacherUser->roles()->syncWithoutDetaching([Role::where('name','teacher')->firstOrFail()->id => ['institution_id'=>$institution->id,'status'=>'active']]);
        $teacher = Teacher::updateOrCreate(['user_id'=>$teacherUser->id], ['institution_id'=>$institution->id,'employee_code'=>'GURU-DEMO','full_name'=>'Guru Demo','nickname'=>'Ustadz Demo','phone'=>$teacherUser->phone,'email'=>$teacherUser->email,'joined_at'=>now()->toDateString(),'specialization'=>'Tahfizh','status'=>'active']);

        $parentUser = User::updateOrCreate(['email'=>'wali.demo@sullamulhifz.or.id'], ['institution_id'=>$institution->id,'name'=>'Wali Demo','phone'=>'6281200000002','password'=>$password,'status'=>'active','email_verified_at'=>now()]);
        $parentUser->roles()->syncWithoutDetaching([Role::where('name','guardian')->firstOrFail()->id => ['institution_id'=>$institution->id,'status'=>'active']]);
        $guardian = Guardian::updateOrCreate(['user_id'=>$parentUser->id], ['institution_id'=>$institution->id,'full_name'=>'Wali Demo','phone'=>$parentUser->phone,'email'=>$parentUser->email,'status'=>'active']);

        $class = SchoolClass::where('institution_id',$institution->id)->where('code','MUSTAWA-AWAL-A')->firstOrFail();

        $program = Program::where('institution_id',$institution->id)->where('code','TAHSIN')->firstOrFail();
        $assignment = TeacherAssignment::updateOrCreate(
            [
                'institution_id'=>$institution->id,
                'academic_year_id'=>$class->academic_year_id,
                'teacher_id'=>$teacher->id,
                'class_id'=>$class->id,
                'program_id'=>$program->id,
            ],
            [
                'learning_group_id'=>null,
                'assignment_role'=>'lead',
                'valid_from'=>now()->startOfYear()->toDateString(),
                'status'=>'active',
            ]
        );
        Schedule::updateOrCreate(
            [
                'institution_id'=>$institution->id,
                'teacher_assignment_id'=>$assignment->id,
                'day_of_week'=>4,
                'start_time'=>'15:30:00',
            ],
            [
                'academic_year_id'=>$class->academic_year_id,
                'class_id'=>$class->id,
                'learning_group_id'=>null,
                'program_id'=>$program->id,
                'end_time'=>'17:30:00',
                'location'=>'Ruang Mustawa Awal A',
                'status'=>'active',
            ]
        );
        foreach (['Alya Demo','Fawwaz Demo','Hasna Demo'] as $index => $name) {
            $student = Student::updateOrCreate(['institution_id'=>$institution->id,'student_code'=>'DEMO-'.str_pad((string)($index+1),3,'0',STR_PAD_LEFT)], ['full_name'=>$name,'nickname'=>strtok($name,' '),'gender'=>$index===1?'male':'female','joined_at'=>now()->toDateString(),'status'=>'active','stifin_status'=>'untested']);
            $student->guardians()->syncWithoutDetaching([$guardian->id => ['relationship'=>$index===0?'mother':'guardian','is_primary_contact'=>true,'can_receive_notifications'=>true,'can_submit_assignments'=>true,'can_view_learning_records'=>true]]);
            ClassEnrollment::updateOrCreate(['class_id'=>$class->id,'student_id'=>$student->id,'academic_year_id'=>$class->academic_year_id], ['enrolled_at'=>now()->toDateString(),'status'=>'active']);
        }
    }
}
