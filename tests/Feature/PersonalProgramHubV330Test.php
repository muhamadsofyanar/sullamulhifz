<?php

namespace Tests\Feature;

use App\Models\PersonalModuleEnrollment;
use App\Models\User;
use App\Services\PersonalModuleAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PersonalProgramHubV330Test extends TestCase
{
    use RefreshDatabase;

    public function test_personal_program_hub_schema_and_routes_exist(): void
    {
        $this->assertTrue(Schema::hasTable('personal_module_enrollments'));
        $this->assertTrue(Route::has('personal.programs.index'));
        $this->assertTrue(Route::has('personal.programs.enroll'));
    }

    public function test_new_personal_home_only_exposes_programs_after_enrollment(): void
    {
        $user = $this->registerPersonal('hub');

        $this->get(route('personal.dashboard'))
            ->assertOk()
            ->assertSee('Belum ada program aktif')
            ->assertDontSee('href="'.route('quran-practice.index').'"', false)
            ->assertDontSee('href="'.route('quran-journey.index').'"', false)
            ->assertDontSee('href="'.route('personal.learning.index').'"', false);

        $this->get(route('quran-practice.index'))->assertForbidden();
        $this->post(route('personal.programs.enroll', 'quran_practice'))->assertRedirect();

        $this->assertDatabaseHas('personal_module_enrollments', [
            'user_id' => $user->id,
            'module_key' => 'quran_practice',
            'status' => 'active',
        ]);

        $this->get(route('personal.dashboard'))
            ->assertOk()
            ->assertSee('href="'.route('quran-practice.index').'"', false)
            ->assertDontSee('href="'.route('quran-journey.index').'"', false);
    }

    public function test_personal_user_can_enable_journey_and_guided_without_unlocking_academy(): void
    {
        $user = $this->registerPersonal('modules');

        $this->post(route('personal.programs.enroll', 'quran_journey'))->assertRedirect();
        $this->post(route('personal.programs.enroll', 'guided_learning'))->assertRedirect();

        $access = app(PersonalModuleAccessService::class);
        $this->assertTrue($access->allows($user, 'quran_journey'));
        $this->assertTrue($access->allows($user, 'guided_learning'));
        $this->assertFalse($access->allows($user, 'academy'));
        $this->assertCount(2, PersonalModuleEnrollment::query()->where('user_id', $user->id)->get());

        $this->get(route('quran-journey.index'))->assertOk();
        $this->get(route('personal.learning.index'))->assertOk();
        $this->get(route('academy.index'))->assertForbidden();
    }

    private function registerPersonal(string $suffix): User
    {
        $this->post(route('personal.register.store'), [
            'name' => 'Personal '.ucfirst($suffix),
            'email' => 'personal-'.$suffix.'@example.test',
            'password' => 'RahasiaAman123',
            'password_confirmation' => 'RahasiaAman123',
            'terms' => '1',
        ])->assertRedirect(route('personal.dashboard'));

        return User::query()->where('email', 'personal-'.$suffix.'@example.test')->firstOrFail();
    }
}
