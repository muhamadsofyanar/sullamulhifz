<?php

namespace Tests\Feature;

/** @phase 4.3 Identity & Relationship Core; @phase 4.8 specialized relationship safety; @phase 6.0 workspace status isolation */

use App\Models\Institution;
use App\Models\Guardian;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use App\Models\UserRelationship;
use App\Models\WorkspaceMembership;
use Database\Seeders\ProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceContextV430Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('INITIAL_ADMIN_PASSWORD=TestAdmin2026Secure');
        putenv('SEED_INITIAL_TPA_DATA=false');
        $this->seed(ProductionSeeder::class);
    }

    public function test_one_account_can_switch_between_authorized_workspaces(): void
    {
        $user = User::firstOrFail();
        $user->update(['must_change_password' => false]);
        $role = Role::where('name', 'institution_admin')->firstOrFail();
        $second = Institution::create([
            'name' => 'Kampus Qurani Test', 'code' => 'KAMPUS-TEST', 'slug' => 'kampus-test',
            'workspace_type' => 'institution', 'institution_type' => 'kampus',
            'privacy_mode' => 'institution', 'onboarding_status' => 'completed',
            'status' => 'active', 'timezone' => 'Asia/Jakarta',
        ]);
        $user->roles()->attach($role->id, ['institution_id' => $second->id, 'status' => 'active']);
        WorkspaceMembership::create([
            'institution_id' => $second->id, 'user_id' => $user->id, 'role_id' => $role->id,
            'membership_type' => 'owner', 'status' => 'active', 'is_default' => false, 'joined_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('workspace.switch'), ['workspace_id' => $second->id])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('workspace_id', $second->id);

        $this->actingAs($user)->withSession(['workspace_id' => $second->id])
            ->get(route('dashboard'))->assertOk()->assertSee('Kampus Qurani Test');
    }

    public function test_relationship_requires_target_acceptance(): void
    {
        $requester = User::firstOrFail();
        $requester->update(['must_change_password' => false]);
        $target = User::create([
            'name' => 'Ustadz Target', 'email' => 'ustadz-target@example.test',
            'password' => 'StrongPassword2026', 'status' => 'active',
        ]);

        $this->actingAs($requester)->post(route('relationships.store'), [
            'email' => $target->email,
            'relationship_type' => 'family_companion',
        ])->assertRedirect();

        $relationship = UserRelationship::firstOrFail();
        $this->assertSame('pending', $relationship->status);

        $this->actingAs($target)->put(route('relationships.respond', $relationship), [
            'decision' => 'accepted',
        ])->assertRedirect();

        $this->assertSame('accepted', $relationship->fresh()->status);
        $this->assertNotNull($relationship->fresh()->accepted_at);
    }

    public function test_user_cannot_switch_to_unrelated_workspace(): void
    {
        $user = User::firstOrFail();
        $user->update(['must_change_password' => false]);
        $unrelated = Institution::create([
            'name' => 'Ruang Tanpa Akses', 'code' => 'NO-ACCESS', 'slug' => 'no-access',
            'workspace_type' => 'institution', 'institution_type' => 'sma',
            'privacy_mode' => 'institution', 'onboarding_status' => 'completed',
            'status' => 'active', 'timezone' => 'Asia/Jakarta',
        ]);

        $this->actingAs($user)->post(route('workspace.switch'), ['workspace_id' => $unrelated->id])->assertForbidden();
    }

    public function test_workspace_scoped_profile_follows_active_institution(): void
    {
        $user = User::firstOrFail();
        $firstInstitution = $user->institution;
        $secondInstitution = Institution::create([
            'name' => 'Ruang Profil Kedua', 'code' => 'PROFILE-SECOND', 'slug' => 'profile-second',
            'workspace_type' => 'institution', 'institution_type' => 'komunitas',
            'privacy_mode' => 'institution', 'onboarding_status' => 'completed',
            'status' => 'active', 'timezone' => 'Asia/Jakarta',
        ]);
        Teacher::create([
            'institution_id' => $firstInstitution->id, 'user_id' => $user->id,
            'full_name' => 'Profil Ruang Pertama', 'status' => 'active',
        ]);
        Teacher::create([
            'institution_id' => $secondInstitution->id, 'user_id' => $user->id,
            'full_name' => 'Profil Ruang Kedua', 'status' => 'active',
        ]);

        $user->setAttribute('institution_id', $secondInstitution->id);
        $user->unsetRelation('teacher');

        $this->assertSame('Profil Ruang Kedua', $user->teacher?->full_name);
    }

    public function test_disabling_guardian_in_one_workspace_does_not_disable_the_global_account(): void
    {
        $admin = User::query()->whereHas('roles', fn ($query) => $query->where('roles.name', 'institution_admin'))->firstOrFail();
        $admin->update(['must_change_password' => false]);
        $first = $admin->institution;
        $second = Institution::create([
            'name' => 'Ruang Wali Kedua', 'code' => 'GUARDIAN-SECOND', 'slug' => 'guardian-second',
            'workspace_type' => 'institution', 'institution_type' => 'tpa',
            'privacy_mode' => 'institution', 'onboarding_status' => 'completed',
            'status' => 'active', 'timezone' => 'Asia/Jakarta',
        ]);
        $guardianRole = Role::where('name', 'guardian')->firstOrFail();
        $guardianUser = User::create([
            'institution_id' => $first->id, 'name' => 'Wali Dua Ruang',
            'email' => 'wali-dua-ruang@example.test', 'password' => 'RahasiaAman123',
            'status' => 'active', 'must_change_password' => false,
        ]);
        $guardian = Guardian::create([
            'institution_id' => $first->id, 'user_id' => $guardianUser->id,
            'full_name' => 'Wali Dua Ruang', 'email' => $guardianUser->email, 'status' => 'active',
        ]);
        foreach ([$first, $second] as $index => $institution) {
            $guardianUser->roles()->attach($guardianRole->id, [
                'institution_id' => $institution->id, 'status' => 'active',
            ]);
            WorkspaceMembership::create([
                'institution_id' => $institution->id, 'user_id' => $guardianUser->id,
                'role_id' => $guardianRole->id, 'membership_type' => 'member',
                'status' => 'active', 'is_default' => $index === 0, 'joined_at' => now(),
            ]);
        }

        $this->actingAs($admin)->put(route('admin.guardians.update', $guardian), [
            'full_name' => 'Wali Dua Ruang',
            'email' => 'wali-dua-ruang@example.test',
            'phone' => null,
            'occupation' => null,
            'address' => null,
            'status' => 'inactive',
        ])->assertRedirect();

        $this->assertSame('active', $guardianUser->fresh()->status);
        $this->assertDatabaseHas('workspace_memberships', [
            'institution_id' => $first->id, 'user_id' => $guardianUser->id, 'status' => 'inactive',
        ]);
        $this->assertDatabaseHas('workspace_memberships', [
            'institution_id' => $second->id, 'user_id' => $guardianUser->id, 'status' => 'active',
        ]);
        $this->assertDatabaseHas('user_roles', [
            'institution_id' => $second->id, 'user_id' => $guardianUser->id,
            'role_id' => $guardianRole->id, 'status' => 'active',
        ]);
    }
}
