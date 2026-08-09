<?php

namespace Tests\Feature;

/** @phase 4.5 Personal 2.0 — identity, safeguarding, portfolio, and dashboard regression */

use App\Http\Controllers\DashboardController;
use App\Models\Guardian;
use App\Models\Institution;
use App\Models\PersonalProfile;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use Database\Seeders\ProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PersonalTwoV450Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('INITIAL_ADMIN_PASSWORD=TestAdmin2026Secure');
        putenv('SEED_INITIAL_TPA_DATA=false');
        $this->seed(ProductionSeeder::class);
    }

    public function test_personal_two_schema_and_private_portfolio_route_exist(): void
    {
        foreach (['age_group', 'interests', 'aspiration', 'quranic_purpose', 'learning_mode', 'safeguarding_acknowledged_at'] as $column) {
            $this->assertTrue(Schema::hasColumn('personal_profiles', $column), "Kolom {$column} belum tersedia.");
        }

        $this->assertTrue(Route::has('personal.portfolio.store'));
    }

    public function test_minor_registration_requires_guardian_acknowledgement_and_saves_context(): void
    {
        $payload = [
            'name' => 'Alya Cita',
            'email' => 'alya-cita@example.test',
            'password' => 'RahasiaAman123',
            'password_confirmation' => 'RahasiaAman123',
            'age_group' => 'child',
            'interests' => ['healthcare', 'education'],
            'aspiration' => 'Dokter anak',
            'learning_mode' => 'with_parent',
            'terms' => '1',
        ];

        $this->post(route('personal.register.store'), $payload)
            ->assertSessionHasErrors('guardian_acknowledgement');

        $this->post(route('personal.register.store'), $payload + ['guardian_acknowledgement' => '1'])
            ->assertRedirect(route('personal.dashboard'));

        $user = User::query()->where('email', 'alya-cita@example.test')->firstOrFail();
        $profile = PersonalProfile::query()->where('user_id', $user->id)->firstOrFail();
        $this->assertSame('child', $profile->age_group);
        $this->assertSame('Dokter anak', $profile->aspiration);
        $this->assertSame('with_parent', $profile->learning_mode);
        $this->assertNotNull($profile->safeguarding_acknowledged_at);

        $this->actingAs($user)->put(route('personal.onboarding'), [
            'age_group' => 'child',
            'interests' => ['healthcare', 'education'],
            'aspiration' => 'Dokter anak',
            'quranic_purpose' => 'Menjaga rahmah, amanah, dan semangat menolong sesama.',
            'learning_mode' => 'with_parent',
            'guardian_acknowledgement' => '1',
            'experience_level' => 'starting',
            'primary_focus' => 'balanced',
            'daily_minutes' => 15,
        ])->assertRedirect();

        $this->get(route('personal.dashboard'))
            ->assertOk()
            ->assertSee('Dokter anak')
            ->assertSee('Tujuan Qur’ani')
            ->assertSee('Bersama orang tua/wali')
            ->assertDontSee('ranking cita-cita');
    }

    public function test_personal_portfolio_is_private_and_not_visible_to_another_personal_user(): void
    {
        $first = $this->registerPersonal('first');

        $this->actingAs($first)->post(route('personal.portfolio.store'), [
            'category' => 'project',
            'title' => 'Merawat kebun kecil',
            'description' => 'Menanam dan menyiram bibit bersama keluarga.',
            'occurred_on' => today()->toDateString(),
            'quranic_value' => 'Menjaga ciptaan Allah',
            'aspiration_connection' => 'Latihan awal menuju cita-cita menjadi ahli tanaman.',
        ])->assertRedirect();

        $this->assertDatabaseHas('student_portfolios', [
            'institution_id' => $first->institution_id,
            'created_by_user_id' => $first->id,
            'title' => 'Merawat kebun kecil',
            'visibility' => 'private',
        ]);
        $this->get(route('personal.journey.index'))->assertOk()->assertSee('Merawat kebun kecil');

        Auth::logout();
        $second = $this->registerPersonal('second');
        $this->actingAs($second)->get(route('personal.journey.index'))
            ->assertOk()
            ->assertDontSee('Merawat kebun kecil');
    }

    public function test_dashboard_resolves_teacher_and_guardian_profiles_after_roles_are_loaded(): void
    {
        $institution = Institution::query()->firstOrFail();

        $teacherUser = User::create([
            'institution_id' => $institution->id,
            'name' => 'Guru Workspace',
            'email' => 'guru-workspace@example.test',
            'password' => 'RahasiaAman123',
            'status' => 'active',
        ]);
        $teacherRole = Role::query()->where('name', 'teacher')->firstOrFail();
        $teacherUser->roles()->attach($teacherRole->id, ['institution_id' => $institution->id, 'status' => 'active']);
        Teacher::create([
            'institution_id' => $institution->id,
            'user_id' => $teacherUser->id,
            'full_name' => 'Guru Workspace',
            'status' => 'active',
        ]);

        $teacherRequest = Request::create('/dashboard', 'GET');
        $teacherRequest->setUserResolver(fn (): User => $teacherUser);
        $teacherView = app(DashboardController::class)($teacherRequest);
        $this->assertSame('dashboard.teacher', $teacherView->name());

        $guardianUser = User::create([
            'institution_id' => $institution->id,
            'name' => 'Wali Workspace',
            'email' => 'wali-workspace@example.test',
            'password' => 'RahasiaAman123',
            'status' => 'active',
        ]);
        $guardianRole = Role::query()->where('name', 'guardian')->firstOrFail();
        $guardianUser->roles()->attach($guardianRole->id, ['institution_id' => $institution->id, 'status' => 'active']);
        Guardian::create([
            'institution_id' => $institution->id,
            'user_id' => $guardianUser->id,
            'full_name' => 'Wali Workspace',
            'status' => 'active',
        ]);

        $guardianRequest = Request::create('/dashboard', 'GET');
        $guardianRequest->setUserResolver(fn (): User => $guardianUser);
        $guardianView = app(DashboardController::class)($guardianRequest);
        $this->assertSame('dashboard.guardian', $guardianView->name());
    }

    private function registerPersonal(string $suffix): User
    {
        $this->post(route('personal.register.store'), [
            'name' => 'Personal '.ucfirst($suffix),
            'email' => 'personal-v450-'.$suffix.'@example.test',
            'password' => 'RahasiaAman123',
            'password_confirmation' => 'RahasiaAman123',
            'terms' => '1',
        ])->assertRedirect(route('personal.dashboard'));

        return User::query()->where('email', 'personal-v450-'.$suffix.'@example.test')->firstOrFail();
    }
}
