<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\LearningObservation;
use App\Models\LearningRecommendationReview;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Services\PersonalLearningRecommendationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PersonalLearningV290Test extends TestCase
{
    use RefreshDatabase;

    public function test_phase_seven_structure_and_routes_exist(): void
    {
        foreach (['learning_observations','learning_insights','student_marhalah_histories','learning_recommendation_reviews'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabel {$table} belum tersedia.");
        }

        $this->assertTrue(Route::has('teacher.personal-learning.index'));
        $this->assertTrue(Route::has('teacher.personal-learning.recommendations.generate'));
        $this->assertTrue(Route::has('teacher.personal-learning.recommendations.review'));
    }

    public function test_recommendation_uses_learning_evidence_and_records_teacher_override(): void
    {
        $institution = Institution::create(['name'=>'TPA Fase 7','code'=>'TPA-F7','slug'=>'tpa-f7','status'=>'active']);
        $user = User::create(['institution_id'=>$institution->id,'name'=>'Guru Fase 7','email'=>'guru-f7@example.test','password'=>'secret-12345','status'=>'active']);
        $teacher = Teacher::create(['institution_id'=>$institution->id,'user_id'=>$user->id,'employee_code'=>'G-F7','full_name'=>'Guru Fase 7','status'=>'active']);
        $student = Student::create(['institution_id'=>$institution->id,'student_code'=>'S-F7','full_name'=>'Santri Fase 7','status'=>'active','stifin_status'=>'tested','stifin_result'=>'TEST-DATA']);

        $observation = LearningObservation::create([
            'institution_id'=>$institution->id,
            'student_id'=>$student->id,
            'teacher_id'=>$teacher->id,
            'category'=>'learning_method',
            'method_name'=>'Pengulangan audio 5 kali',
            'response'=>'Santri lebih siap menyetor setelah mendengar dan menirukan.',
            'effectiveness'=>'helpful',
            'observed_at'=>now(),
        ]);

        $insight = app(PersonalLearningRecommendationService::class)->generate($student,$teacher,$user->id);
        $this->assertSame('pending_review',$insight->status);
        $this->assertContains($observation->id,$insight->evidence['observation_ids']);
        $this->assertStringNotContainsStringIgnoringCase('stifin',$insight->summary);
        $this->assertStringNotContainsString('TEST-DATA',json_encode($insight->evidence));

        LearningRecommendationReview::create([
            'institution_id'=>$institution->id,
            'learning_insight_id'=>$insight->id,
            'student_id'=>$student->id,
            'teacher_id'=>$teacher->id,
            'decision'=>'modified',
            'original_recommendation'=>$insight->summary,
            'final_recommendation'=>'Gunakan pengulangan audio 3 kali, lalu evaluasi kembali respons santri.',
            'review_note'=>'Guru menyesuaikan beban berdasarkan kondisi pertemuan.',
            'reviewed_at'=>now(),
        ]);

        $this->assertDatabaseHas('learning_recommendation_reviews',[
            'learning_insight_id'=>$insight->id,
            'decision'=>'modified',
        ]);
    }
}
