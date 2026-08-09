<?php

namespace Tests\Feature;

/** @phase 4.6 Private Ustadz; @phase 4.7 Institution Suite; @phase 4.8 Family & Parent Portal */

use App\Models\Institution;
use App\Models\MentorshipSession;
use App\Models\PersonalGoal;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use App\Models\UserRelationship;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMembership;
use Database\Seeders\ProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductExpansionV480Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('INITIAL_ADMIN_PASSWORD=TestAdmin2026Secure');
        putenv('SEED_INITIAL_TPA_DATA=false');
        $this->seed(ProductionSeeder::class);
    }

    public function test_combined_release_schema_and_routes_are_available(): void
    {
        $this->assertTrue(Schema::hasTable('mentorship_sessions'));
        $this->assertTrue(Schema::hasTable('family_support_notes'));
        foreach ([
            'mentorship.index', 'mentorship.sessions.store', 'family.index', 'family.notes.store',
            'admin.institution-suite.index', 'institution-suite.invitations.accept',
        ] as $route) {
            $this->assertTrue(Route::has($route), "Route {$route} belum tersedia.");
        }
    }

    public function test_private_mentor_requires_consent_and_only_shares_selected_scopes(): void
    {
        $learner = $this->registerPersonal('mentor-learner', 'adult');
        $teacher = $this->createTeacher('mentor-teacher');

        $this->actingAs($learner)->post(route('mentorship.relationships.store'), [
            'email' => $teacher->email,
            'visibility_scope' => ['progress_summary', 'goals'],
        ])->assertRedirect();

        $relationship = UserRelationship::query()->where('relationship_type', 'mentor_learner')->firstOrFail();
        $this->assertSame($learner->id, $relationship->from_user_id);
        $this->assertSame($teacher->id, $relationship->to_user_id);
        $this->assertSame('pending', $relationship->status);

        $this->actingAs($teacher)->put(route('mentorship.relationships.respond', $relationship), [
            'decision' => 'accepted',
        ])->assertRedirect();
        $this->assertSame('accepted', $relationship->fresh()->status);

        PersonalGoal::create([
            'institution_id' => $learner->institution_id,
            'personal_profile_id' => $learner->personalProfile->id,
            'user_id' => $learner->id,
            'title' => 'Murajaah Al-Fatihah',
            'metric' => 'session',
            'target_value' => 10,
            'progress_value' => 2,
            'starts_on' => today(),
            'status' => 'active',
        ]);

        $this->actingAs($teacher)->get(route('mentorship.index'))
            ->assertOk()
            ->assertSee('Murajaah Al-Fatihah')
            ->assertDontSee('Judul portofolio yang diizinkan');
    }

    public function test_mentor_session_lifecycle_and_participant_isolation(): void
    {
        [$learner, $teacher, $relationship] = $this->acceptedMentorship();

        $this->actingAs($learner)->post(route('mentorship.sessions.store', $relationship), [
            'focus' => 'Tajwid Al-Fatihah',
            'learner_note' => 'Mohon fokus pada mad.',
            'duration_minutes' => 30,
        ])->assertRedirect();
        $session = MentorshipSession::firstOrFail();
        $this->assertSame('requested', $session->status);

        $this->actingAs($teacher)->put(route('mentorship.sessions.update', $session), [
            'status' => 'completed',
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'duration_minutes' => 30,
            'mentor_note' => 'Mad sudah lebih stabil, lanjutkan latihan perlahan.',
        ])->assertRedirect();
        $this->assertSame('completed', $session->fresh()->status);
        $this->assertNotNull($session->fresh()->completed_at);

        $outsider = $this->registerPersonal('mentor-outsider', 'adult');
        $this->actingAs($outsider)->put(route('mentorship.sessions.update', $session), ['status' => 'cancelled'])->assertForbidden();
    }

    public function test_minor_cannot_start_private_mentoring_before_family_consent(): void
    {
        $minor = $this->registerPersonal('minor-no-family', 'teen');
        $teacher = $this->createTeacher('minor-teacher');

        $this->actingAs($minor)->post(route('mentorship.relationships.store'), [
            'email' => $teacher->email,
        ])->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('user_relationships', ['relationship_type' => 'mentor_learner', 'from_user_id' => $minor->id]);
    }

    public function test_family_portal_consent_notes_and_child_owned_visibility(): void
    {
        $child = $this->registerPersonal('family-child', 'child');
        $guardian = $this->registerPersonal('family-guardian', 'adult');

        $this->actingAs($child)->post(route('family.relationships.store'), ['email' => $guardian->email])->assertRedirect();
        $relationship = UserRelationship::query()->where('relationship_type', 'guardian_child')->firstOrFail();
        $this->assertSame($child->id, $relationship->from_user_id);
        $this->assertSame($guardian->id, $relationship->to_user_id);

        $this->actingAs($guardian)->put(route('family.relationships.respond', $relationship), ['decision' => 'accepted'])->assertRedirect();
        $this->actingAs($child)->put(route('family.relationships.consent', $relationship), [
            'visibility_scope' => ['progress_summary', 'goals', 'practice'],
        ])->assertRedirect();
        $this->actingAs($guardian)->put(route('family.relationships.consent', $relationship), [
            'visibility_scope' => ['portfolio'],
        ])->assertForbidden();

        $this->actingAs($guardian)->post(route('family.notes.store', $relationship), [
            'note_type' => 'encouragement',
            'body' => 'Hari ini membaca dengan lebih tenang.',
            'observed_on' => today()->toDateString(),
        ])->assertRedirect();
        $this->assertDatabaseHas('family_support_notes', [
            'child_user_id' => $child->id,
            'author_user_id' => $guardian->id,
            'status' => 'visible',
        ]);
        $this->actingAs($child)->get(route('family.index'))->assertOk()->assertSee('Hari ini membaca dengan lebih tenang.');
    }

    public function test_institution_invitation_adds_a_workspace_without_replacing_personal_space(): void
    {
        $admin = $this->institutionAdmin();
        $institution = $admin->institution;
        $personal = $this->registerPersonal('workspace-invitee', 'adult');
        $personalWorkspaceId = $personal->institution_id;

        $response = $this->actingAs($admin)->post(route('admin.institution-suite.invitations.store'), [
            'email' => $personal->email,
            'role' => 'teacher',
        ])->assertRedirect();
        $invitationUrl = $response->getSession()->get('institution_invitation_url');
        $this->assertNotEmpty($invitationUrl);
        $token = Str::afterLast($invitationUrl, '/');

        $this->actingAs($personal)->get(route('institution-suite.invitations.show', ['token' => $token]))
            ->assertOk()
            ->assertSee($institution->name);
        $this->actingAs($personal)->post(route('institution-suite.invitations.accept', ['token' => $token]))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('workspace_memberships', [
            'institution_id' => $institution->id,
            'user_id' => $personal->id,
            'membership_type' => 'teacher',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('workspace_memberships', [
            'institution_id' => $personalWorkspaceId,
            'user_id' => $personal->id,
            'membership_type' => 'learner',
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('teachers', ['institution_id' => $institution->id, 'user_id' => $personal->id, 'status' => 'active']);
        $this->assertSame('accepted', WorkspaceInvitation::firstOrFail()->status);
    }

    public function test_member_suspension_is_scoped_to_one_institution(): void
    {
        $admin = $this->institutionAdmin();
        $personal = $this->registerPersonal('workspace-suspend', 'adult');
        $personalMembership = WorkspaceMembership::query()->where('user_id', $personal->id)->where('membership_type', 'learner')->firstOrFail();
        $teacherRole = Role::query()->where('name', 'teacher')->firstOrFail();
        $institutionMembership = WorkspaceMembership::create([
            'institution_id' => $admin->institution_id,
            'user_id' => $personal->id,
            'role_id' => $teacherRole->id,
            'membership_type' => 'teacher',
            'status' => 'active',
            'is_default' => false,
            'joined_at' => now(),
        ]);
        DB::table('user_roles')->updateOrInsert(
            ['user_id' => $personal->id, 'role_id' => $teacherRole->id, 'institution_id' => $admin->institution_id],
            ['status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        );

        $this->actingAs($admin)->put(route('admin.institution-suite.members.update', $institutionMembership), ['status' => 'suspended'])->assertRedirect();

        $this->assertSame('suspended', $institutionMembership->fresh()->status);
        $this->assertSame('active', $personalMembership->fresh()->status);
        $this->assertSame('active', $personal->fresh()->status);
    }

    public function test_workspace_invitation_rejects_wrong_account_and_cross_tenant_member_updates(): void
    {
        $admin = $this->institutionAdmin();
        $invitee = $this->registerPersonal('right-invitee', 'adult');
        $wrong = $this->registerPersonal('wrong-invitee', 'adult');

        $response = $this->actingAs($admin)->post(route('admin.institution-suite.invitations.store'), [
            'email' => $invitee->email,
            'role' => 'guardian',
        ]);
        $token = Str::afterLast((string) $response->getSession()->get('institution_invitation_url'), '/');
        $this->actingAs($wrong)->get(route('institution-suite.invitations.show', ['token' => $token]))->assertForbidden();

        $otherInstitution = Institution::create([
            'name' => 'Lembaga Lain', 'code' => 'OTHER-SPACE', 'slug' => 'other-space',
            'workspace_type' => 'institution', 'institution_type' => 'smp', 'privacy_mode' => 'institution',
            'onboarding_status' => 'completed', 'status' => 'active', 'timezone' => 'Asia/Jakarta',
        ]);
        $otherMembership = WorkspaceMembership::create([
            'institution_id' => $otherInstitution->id, 'user_id' => $wrong->id,
            'membership_type' => 'member', 'status' => 'active', 'is_default' => false, 'joined_at' => now(),
        ]);
        $this->actingAs($admin)->put(route('admin.institution-suite.members.update', $otherMembership), ['status' => 'suspended'])->assertForbidden();
    }

    private function registerPersonal(string $suffix, string $ageGroup): User
    {
        Auth::logout();
        $payload = [
            'name' => 'Personal '.Str::headline($suffix),
            'email' => $suffix.'@example.test',
            'password' => 'RahasiaAman123',
            'password_confirmation' => 'RahasiaAman123',
            'age_group' => $ageGroup,
            'learning_mode' => in_array($ageGroup, ['child', 'teen'], true) ? 'with_parent' : 'self',
            'terms' => '1',
        ];
        if (in_array($ageGroup, ['child', 'teen'], true)) {
            $payload['guardian_acknowledgement'] = '1';
        }
        $this->post(route('personal.register.store'), $payload)->assertRedirect(route('personal.dashboard'));

        return User::query()->where('email', $suffix.'@example.test')->firstOrFail();
    }

    private function createTeacher(string $suffix): User
    {
        $institution = Institution::query()->where('workspace_type', 'institution')->firstOrFail();
        $user = User::create([
            'institution_id' => $institution->id,
            'name' => 'Ustadz '.Str::headline($suffix),
            'email' => $suffix.'@example.test',
            'password' => 'RahasiaAman123',
            'status' => 'active',
            'must_change_password' => false,
        ]);
        $role = Role::query()->where('name', 'teacher')->firstOrFail();
        $user->roles()->attach($role->id, ['institution_id' => $institution->id, 'status' => 'active']);
        WorkspaceMembership::create([
            'institution_id' => $institution->id, 'user_id' => $user->id, 'role_id' => $role->id,
            'membership_type' => 'teacher', 'status' => 'active', 'is_default' => true, 'joined_at' => now(),
        ]);
        Teacher::create([
            'institution_id' => $institution->id, 'user_id' => $user->id,
            'full_name' => $user->name, 'email' => $user->email, 'status' => 'active',
        ]);

        return $user;
    }

    private function acceptedMentorship(): array
    {
        $learner = $this->registerPersonal('accepted-learner', 'adult');
        $teacher = $this->createTeacher('accepted-teacher');
        $relationship = UserRelationship::create([
            'context_key' => 'private-mentorship',
            'from_user_id' => $learner->id,
            'to_user_id' => $teacher->id,
            'created_by_user_id' => $learner->id,
            'relationship_type' => 'mentor_learner',
            'status' => 'accepted',
            'visibility_scope' => ['progress_summary'],
            'starts_at' => now(),
            'accepted_at' => now(),
        ]);

        return [$learner, $teacher, $relationship];
    }

    private function institutionAdmin(): User
    {
        $admin = User::query()->whereHas('roles', fn ($query) => $query->where('name', 'institution_admin'))->firstOrFail();
        $admin->update(['must_change_password' => false]);

        return $admin->fresh('institution');
    }
}
