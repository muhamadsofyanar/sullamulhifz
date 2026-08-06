<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\ProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_available(): void
    {
        $this->get('/login')->assertOk()->assertSee('Sullamul Ḥifẓ');
    }

    public function test_initial_admin_is_forced_to_change_password(): void
    {
        $this->seed(ProductionSeeder::class);
        $user = User::where('email', env('INITIAL_ADMIN_EMAIL', 'admin@sullamulhifz.id'))->firstOrFail();

        $this->actingAs($user)->get('/dashboard')->assertRedirect(route('profile.edit'));
    }

    public function test_admin_can_open_dashboard_after_changing_password(): void
    {
        $this->seed(ProductionSeeder::class);
        $user = User::where('email', env('INITIAL_ADMIN_EMAIL', 'admin@sullamulhifz.id'))->firstOrFail();
        $user->update(['must_change_password' => false]);

        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('Beranda Admin');
    }

    public function test_role_middleware_blocks_unrelated_role(): void
    {
        $this->seed(ProductionSeeder::class);
        $institutionId = User::firstOrFail()->institution_id;
        $user = User::create([
            'institution_id'=>$institutionId,
            'name'=>'Wali Test',
            'email'=>'wali@test.local',
            'password'=>'password-test',
            'status'=>'active',
        ]);
        $role = Role::where('name','guardian')->firstOrFail();
        $user->roles()->attach($role->id,['institution_id'=>$institutionId,'status'=>'active']);

        $this->actingAs($user)->get('/admin/students')->assertForbidden();
    }
}
