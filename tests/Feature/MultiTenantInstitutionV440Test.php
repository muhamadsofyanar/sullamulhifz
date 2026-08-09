<?php

namespace Tests\Feature;

/** @phase 4.4 Multi-tenant Institution Foundation */

use App\Models\Institution;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\ProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenantInstitutionV440Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('INITIAL_ADMIN_PASSWORD=TestAdmin2026Secure');
        putenv('SEED_INITIAL_TPA_DATA=false');
        $this->seed(ProductionSeeder::class);
    }

    public function test_public_registration_creates_onboarding_workspace_and_owner_membership(): void
    {
        $response = $this->post(route('institution.register.store'), [
            'institution_name' => 'SMA Qurani Nusantara',
            'institution_type' => 'sma',
            'admin_name' => 'Admin SMA',
            'email' => 'admin-sma@example.test',
            'phone' => '081234567890',
            'password' => 'SecureInstitution2026',
            'password_confirmation' => 'SecureInstitution2026',
            'terms' => '1',
        ]);

        $response->assertRedirect(route('dashboard'));
        $institution = Institution::where('name', 'SMA Qurani Nusantara')->firstOrFail();
        $this->assertSame('sma', $institution->institution_type);
        $this->assertSame('onboarding', $institution->status);
        $this->assertSame('Siswa', $institution->term('student'));
        $this->assertDatabaseHas('workspace_memberships', [
            'institution_id' => $institution->id,
            'membership_type' => 'owner',
            'status' => 'active',
        ]);

        $institution->update([
            'brand_primary_color' => '#123456',
            'brand_secondary_color' => '#abcdef',
        ]);

        $this->artisan('sullam:verify-identity-core')->assertExitCode(0);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('menunggu pemeriksaan platform')
            ->assertSee('--emerald:#123456', false)
            ->assertSee('--gold:#abcdef', false);
        $this->get(route('admin.students.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('warning');
    }

    public function test_superadmin_can_approve_registered_workspace(): void
    {
        $admin = User::firstOrFail();
        $admin->update(['must_change_password' => false]);
        $superadmin = Role::where('name', 'superadmin')->firstOrFail();
        $admin->roles()->attach($superadmin->id, ['institution_id' => $admin->institution_id, 'status' => 'active']);
        $institution = Institution::create([
            'name' => 'Kampus Menunggu', 'code' => 'WAITING-CAMPUS', 'slug' => 'waiting-campus',
            'workspace_type' => 'institution', 'institution_type' => 'kampus',
            'privacy_mode' => 'institution', 'onboarding_status' => 'pending_review',
            'status' => 'onboarding', 'timezone' => 'Asia/Jakarta',
        ]);

        $this->actingAs($admin)->put(route('admin.workspaces.status', $institution), ['status' => 'active'])
            ->assertRedirect();

        $institution->refresh();
        $this->assertSame('active', $institution->status);
        $this->assertSame('completed', $institution->onboarding_status);
        $this->assertNotNull($institution->approved_at);
    }
}
