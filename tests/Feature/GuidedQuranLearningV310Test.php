<?php

namespace Tests\Feature;

use App\Models\GuidedQuranProgram;
use App\Models\Institution;
use App\Models\PersonalProfile;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GuidedQuranLearningV310Test extends TestCase
{
    use RefreshDatabase;

    public function test_guided_learning_schema_routes_and_personal_permissions_exist(): void
    {
        foreach (['guided_quran_programs','guided_quran_program_reviewers','guided_quran_enrollments','quran_guided_submissions','quran_guided_submission_reviews'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabel {$table} belum tersedia.");
        }
        foreach (['personal.learning.index','personal.learning.enroll','personal.learning.submit','guided-review.index','guided-review.review','admin.guided-learning.index'] as $route) {
            $this->assertTrue(Route::has($route), "Route {$route} belum tersedia.");
        }

        $this->post(route('personal.register.store'), [
            'name' => 'Learner Guided', 'email' => 'guided@example.test', 'password' => 'RahasiaAman123',
            'password_confirmation' => 'RahasiaAman123', 'terms' => '1',
        ])->assertRedirect(route('personal.dashboard'));

        $user = User::where('email', 'guided@example.test')->firstOrFail();
        $this->assertTrue($user->hasPermission('guided_learning.use'));
        $this->assertTrue($user->hasPermission('academy.view'));
        $this->assertTrue($user->hasPermission('quran.view'));
        $this->get(route('personal.learning.index'))->assertOk()->assertSee('Belajar Al-Qur’an tidak harus sendirian');
    }

    public function test_personal_user_cannot_submit_to_another_personal_users_enrollment(): void
    {
        [$userA, $profileA] = $this->registerPersonal('a');
        [$userB] = $this->registerPersonal('b');

        $provider = Institution::create(['name'=>'Provider Quran','code'=>'PROVIDER-Q','slug'=>'provider-q','status'=>'active']);
        $program = GuidedQuranProgram::create([
            'provider_institution_id'=>$provider->id,
            'title'=>'Tahfizh Juz 30 Online',
            'slug'=>'tahfizh-juz-30-online',
            'program_type'=>'tahfizh',
            'delivery_mode'=>'online',
            'accepts_audio'=>true,
            'accepts_text'=>true,
            'is_public'=>true,
            'status'=>'published',
        ]);

        $this->actingAs($userA)->post(route('personal.learning.enroll', $program))->assertRedirect();
        $enrollment = \App\Models\GuidedQuranEnrollment::where('student_id', $profileA->student_id)->firstOrFail();

        $this->actingAs($userB)->post(route('personal.learning.submit', $enrollment), [
            'submission_type'=>'memorization', 'surah_id'=>1, 'start_verse'=>1, 'end_verse'=>1, 'evidence_text'=>'Tidak boleh masuk.',
        ])->assertNotFound();
        $this->assertDatabaseCount('quran_guided_submissions', 0);
    }

    private function registerPersonal(string $suffix): array
    {
        $this->post(route('personal.register.store'), [
            'name'=>'Personal '.strtoupper($suffix), 'email'=>'personal-'.$suffix.'@example.test', 'password'=>'RahasiaAman123',
            'password_confirmation'=>'RahasiaAman123', 'terms'=>'1',
        ]);
        $user = User::where('email', 'personal-'.$suffix.'@example.test')->firstOrFail();
        $profile = PersonalProfile::where('user_id', $user->id)->firstOrFail();
        auth()->logout();
        return [$user, $profile];
    }
}
