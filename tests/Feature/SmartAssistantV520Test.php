<?php

namespace Tests\Feature;

/** @phase 5.2 Smart Assistant with human review */

use App\Models\AiAssistDraft;
use App\Models\Institution;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\User;
use App\Models\UserRelationship;
use App\Models\WorkspaceMembership;
use Database\Seeders\ProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

class SmartAssistantV520Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('INITIAL_ADMIN_PASSWORD=TestAdmin2026Secure');
        putenv('SEED_INITIAL_TPA_DATA=false');
        $this->seed(ProductionSeeder::class);
    }

    public function test_personal_guidance_requires_human_review_and_allows_consented_cross_workspace_mentor(): void
    {
        $learner = $this->registerPersonal('smart-learner');
        $teacher = $this->createTeacher('smart-teacher');
        UserRelationship::create([
            'context_key' => 'private-mentorship',
            'from_user_id' => $learner->id,
            'to_user_id' => $teacher->id,
            'created_by_user_id' => $learner->id,
            'relationship_type' => 'mentor_learner',
            'status' => 'accepted',
            'visibility_scope' => ['progress_summary', 'goals'],
            'starts_at' => now(),
            'accepted_at' => now(),
        ]);

        $this->actingAs($learner)->get(route('personal.smart-assistant.index'))
            ->assertOk()->assertSee('Saran yang membantu, keputusan tetap manusiawi');
        $this->actingAs($learner)->post(route('personal.smart-assistant.review-request'))->assertRedirect();

        $draft = AiAssistDraft::query()->where('created_by_user_id', $learner->id)->firstOrFail();
        $this->assertSame('pending_review', $draft->status);
        $this->assertNull($draft->review);

        $this->actingAs($teacher)->get(route('teacher.smart-assistant.index'))
            ->assertOk()->assertSee($learner->name);
        $this->actingAs($teacher)->put(route('teacher.smart-assistant.review', $draft), [
            'decision' => 'accepted',
            'review_note' => 'Arahan sesuai, lanjutkan dengan ritme ringan.',
        ])->assertRedirect();

        $this->assertSame('approved', $draft->fresh()->status);
        $this->assertDatabaseHas('ai_assist_reviews', ['ai_assist_draft_id' => $draft->id, 'reviewer_user_id' => $teacher->id, 'decision' => 'accepted']);
        $this->assertDatabaseHas('activity_logs', ['action' => 'ai_assist.reviewed', 'subject_id' => $draft->id]);
    }

    private function registerPersonal(string $suffix): User
    {
        Auth::logout();
        $this->post(route('personal.register.store'), [
            'name' => 'Personal '.Str::headline($suffix),
            'email' => $suffix.'@example.test',
            'password' => 'RahasiaAman123',
            'password_confirmation' => 'RahasiaAman123',
            'age_group' => 'adult',
            'learning_mode' => 'self',
            'terms' => '1',
        ])->assertRedirect(route('personal.dashboard'));
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
}
