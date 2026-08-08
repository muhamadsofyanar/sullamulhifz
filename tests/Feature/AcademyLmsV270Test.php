<?php

namespace Tests\Feature;

use App\Models\AcademyLesson;
use App\Models\AcademyLessonProgress;
use App\Models\AcademyModule;
use App\Models\AcademyPrerequisite;
use App\Models\AcademyProgram;
use App\Models\AcademyQuiz;
use App\Models\AcademyQuizAttempt;
use App\Models\AcademyWorksheet;
use App\Models\AcademyWorksheetSubmission;
use App\Models\Institution;
use App\Models\User;
use App\Services\AcademyLmsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AcademyLmsV270Test extends TestCase
{
    use RefreshDatabase;

    public function test_phase_five_lms_tables_exist(): void
    {
        foreach ([
            'academy_prerequisites','academy_quizzes','academy_quiz_questions','academy_quiz_options',
            'academy_quiz_attempts','academy_quiz_answers','academy_worksheets',
            'academy_worksheet_submissions','academy_certificates',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabel {$table} belum tersedia.");
        }
    }

    public function test_prerequisite_quiz_worksheet_and_certificate_flow(): void
    {
        $institution = Institution::create(['name'=>'TPA LMS','code'=>'TPA-LMS','slug'=>'tpa-lms','status'=>'active']);
        $user = User::create(['institution_id'=>$institution->id,'name'=>'Peserta LMS','email'=>'lms@example.test','password'=>'secret-12345','status'=>'active']);
        $program = AcademyProgram::create(['institution_id'=>$institution->id,'title'=>'Program Uji','slug'=>'program-uji','audience'=>'all','status'=>'published']);
        $module = AcademyModule::create(['academy_program_id'=>$program->id,'title'=>'Modul Uji','status'=>'published','sort_order'=>1]);
        $first = AcademyLesson::create(['academy_module_id'=>$module->id,'title'=>'Materi Pertama','slug'=>'materi-pertama','lesson_type'=>'article','status'=>'published','sort_order'=>1]);
        $second = AcademyLesson::create(['academy_module_id'=>$module->id,'title'=>'Materi Kedua','slug'=>'materi-kedua','lesson_type'=>'article','status'=>'published','sort_order'=>2]);
        AcademyPrerequisite::create(['institution_id'=>$institution->id,'subject_type'=>'lesson','subject_id'=>$second->id,'required_type'=>'lesson','required_id'=>$first->id]);

        $lms = app(AcademyLmsService::class);
        $this->assertFalse($lms->isUnlocked($user, 'lesson', $second->id));
        AcademyLessonProgress::create(['institution_id'=>$institution->id,'user_id'=>$user->id,'academy_lesson_id'=>$first->id,'status'=>'completed','progress_percent'=>100,'started_at'=>now(),'completed_at'=>now()]);
        $this->assertTrue($lms->isUnlocked($user, 'lesson', $second->id));

        $quiz = AcademyQuiz::create(['academy_lesson_id'=>$second->id,'title'=>'Kuis Uji','passing_percent'=>70,'max_attempts'=>3,'status'=>'published']);
        $this->assertFalse($lms->lessonRequirementsComplete($user, $second));
        AcademyQuizAttempt::create(['institution_id'=>$institution->id,'user_id'=>$user->id,'academy_quiz_id'=>$quiz->id,'attempt_number'=>1,'score'=>1,'max_score'=>1,'percent'=>100,'passed'=>true,'completed_at'=>now()]);

        $worksheet = AcademyWorksheet::create(['academy_lesson_id'=>$second->id,'title'=>'Worksheet Uji','completion_mode'=>'reflection','is_required'=>true,'status'=>'published']);
        $this->assertFalse($lms->lessonRequirementsComplete($user, $second));
        AcademyWorksheetSubmission::create(['institution_id'=>$institution->id,'user_id'=>$user->id,'academy_worksheet_id'=>$worksheet->id,'response'=>'Tindak lanjut','status'=>'completed','completed_at'=>now()]);
        $this->assertTrue($lms->lessonRequirementsComplete($user, $second));

        AcademyLessonProgress::create(['institution_id'=>$institution->id,'user_id'=>$user->id,'academy_lesson_id'=>$second->id,'status'=>'completed','progress_percent'=>100,'started_at'=>now(),'completed_at'=>now()]);
        $certificate = $lms->issueCertificateIfEligible($user, $program);
        $this->assertNotNull($certificate);
        $this->assertSame('issued', $certificate->status);
        $this->assertDatabaseHas('academy_certificates', ['user_id'=>$user->id,'academy_program_id'=>$program->id]);
    }
}
