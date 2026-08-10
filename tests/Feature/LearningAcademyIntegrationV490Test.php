<?php

namespace Tests\Feature;

/** @phase 4.9 Learning & Academy Integration — unified learning hub and privacy regression */

use App\Models\PersonalGoal;
use App\Models\User;
use Database\Seeders\ProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LearningAcademyIntegrationV490Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('INITIAL_ADMIN_PASSWORD=TestAdmin2026Secure');
        putenv('SEED_INITIAL_TPA_DATA=false');
        $this->seed(ProductionSeeder::class);
    }

    public function test_unified_learning_hub_route_exists_and_is_visible_to_personal(): void
    {
        $this->assertTrue(Route::has('personal.learning-hub.index'));
        $user = $this->registerPersonal('hub-owner');

        $this->actingAs($user)
            ->get(route('personal.learning-hub.index'))
            ->assertOk()
            ->assertSee('Satu tempat untuk melanjutkan belajar')
            ->assertSee('MESIN BELAJAR')
            ->assertSee('Jurnal Personal dan isi portofolio tetap tidak dibuka otomatis');
    }

    public function test_hub_does_not_mix_personal_targets_between_accounts(): void
    {
        $first = $this->registerPersonal('hub-first');
        $firstProfile = $first->personalProfile()->firstOrFail();
        PersonalGoal::create([
            'institution_id' => $first->institution_id,
            'personal_profile_id' => $firstProfile->id,
            'user_id' => $first->id,
            'title' => 'Target milik akun pertama',
            'metric' => 'sessions',
            'target_value' => 5,
            'progress_value' => 1,
            'starts_on' => today(),
            'status' => 'active',
        ]);

        Auth::logout();
        $second = $this->registerPersonal('hub-second');
        $secondProfile = $second->personalProfile()->firstOrFail();
        PersonalGoal::create([
            'institution_id' => $second->institution_id,
            'personal_profile_id' => $secondProfile->id,
            'user_id' => $second->id,
            'title' => 'Target milik akun kedua',
            'metric' => 'sessions',
            'target_value' => 3,
            'progress_value' => 0,
            'starts_on' => today(),
            'status' => 'active',
        ]);

        $this->actingAs($second)
            ->get(route('personal.learning-hub.index'))
            ->assertOk()
            ->assertSee('Target milik akun kedua')
            ->assertDontSee('Target milik akun pertama');
    }

    private function registerPersonal(string $suffix): User
    {
        Auth::logout();
        $this->post(route('personal.register.store'), [
            'name' => 'Personal '.ucfirst($suffix),
            'email' => $suffix.'@example.test',
            'password' => 'RahasiaAman123',
            'password_confirmation' => 'RahasiaAman123',
            'age_group' => 'adult',
            'learning_mode' => 'self',
            'terms' => '1',
        ])->assertRedirect(route('personal.dashboard'));

        return User::query()->where('email', $suffix.'@example.test')->firstOrFail();
    }
}
