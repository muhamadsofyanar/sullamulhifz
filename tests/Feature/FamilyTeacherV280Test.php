<?php

namespace Tests\Feature;

use App\Models\FamilyLearningActivity;
use App\Models\Institution;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherCompetency;
use App\Models\TeacherCompetencyProgress;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FamilyTeacherV280Test extends TestCase
{
    use RefreshDatabase;

    public function test_phase_six_tables_and_routes_exist_without_ranking_columns(): void
    {
        foreach (['family_learning_activities','teacher_competencies','teacher_competency_progress'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabel {$table} belum tersedia.");
        }

        foreach (['score','points','rank','rating'] as $column) {
            $this->assertFalse(Schema::hasColumn('family_learning_activities',$column));
            $this->assertFalse(Schema::hasColumn('teacher_competency_progress',$column));
        }

        $this->assertTrue(Route::has('teacher.family-learning.index'));
        $this->assertTrue(Route::has('guardian.family-learning.index'));
        $this->assertTrue(Route::has('admin.family-teacher.index'));
    }

    public function test_family_activity_and_teacher_competency_store_reflection_lifecycle(): void
    {
        $institution = Institution::create(['name'=>'TPA Fase 6','code'=>'TPA-F6','slug'=>'tpa-f6','status'=>'active']);
        $teacherUser = User::create(['institution_id'=>$institution->id,'name'=>'Guru Fase 6','email'=>'guru-f6@example.test','password'=>'secret-12345','status'=>'active']);
        $guardianUser = User::create(['institution_id'=>$institution->id,'name'=>'Wali Fase 6','email'=>'wali-f6@example.test','password'=>'secret-12345','status'=>'active']);
        $teacher = Teacher::create(['institution_id'=>$institution->id,'user_id'=>$teacherUser->id,'employee_code'=>'G-F6','full_name'=>'Guru Fase 6','status'=>'active']);
        $student = Student::create(['institution_id'=>$institution->id,'student_code'=>'S-F6','full_name'=>'Santri Fase 6','status'=>'active']);

        $activity = FamilyLearningActivity::create([
            'institution_id'=>$institution->id,
            'student_id'=>$student->id,
            'created_by_user_id'=>$teacherUser->id,
            'title'=>'Percakapan 10 menit',
            'activity_type'=>'conversation',
            'instructions'=>'Dengar cerita anak tanpa membandingkan.',
            'status'=>'assigned',
        ]);
        $activity->update(['status'=>'completed','completed_by_user_id'=>$guardianUser->id,'guardian_reflection'=>'Anak lebih nyaman bercerita setelah didengarkan.','completed_at'=>now()]);
        $activity->update(['status'=>'reviewed','teacher_follow_up'=>'Pertahankan percakapan singkat ini pekan depan.','reviewed_at'=>now()]);
        $this->assertSame('reviewed',$activity->fresh()->status);
        $this->assertNotNull($activity->fresh()->guardian_reflection);

        $competency = TeacherCompetency::create([
            'institution_id'=>$institution->id,
            'code'=>'komunikasi-keluarga',
            'title'=>'Komunikasi hangat dengan keluarga',
            'category'=>'family_communication',
            'status'=>'active',
        ]);
        $progress = TeacherCompetencyProgress::create([
            'institution_id'=>$institution->id,
            'teacher_id'=>$teacher->id,
            'teacher_competency_id'=>$competency->id,
            'status'=>'reflection_submitted',
            'reflection'=>'Saya memakai observasi spesifik dan menghindari label.',
            'submitted_at'=>now(),
        ]);
        $progress->update(['status'=>'demonstrated','reviewed_by_user_id'=>$teacherUser->id,'review_note'=>'Praktik terkonfirmasi melalui refleksi.','reviewed_at'=>now()]);

        $this->assertSame('demonstrated',$progress->fresh()->status);
        $this->assertDatabaseCount('family_learning_activities',1);
        $this->assertDatabaseCount('teacher_competency_progress',1);
    }
}
