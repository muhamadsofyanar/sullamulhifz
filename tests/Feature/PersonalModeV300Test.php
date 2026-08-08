<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\PersonalGoal;
use App\Models\PersonalProfile;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use App\Services\PersonalJourneyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PersonalModeV300Test extends TestCase
{
    use RefreshDatabase;

    public function test_personal_mode_schema_and_routes_exist(): void
    {
        $this->assertTrue(Schema::hasColumn('institutions','workspace_type'));
        foreach (['personal_profiles','personal_goals','personal_practice_entries'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabel {$table} belum tersedia.");
        }
        $this->assertTrue(Route::has('personal.register'));
        $this->assertTrue(Route::has('personal.dashboard'));
        $this->assertTrue(Route::has('personal.activities.store'));
        $this->assertTrue(Route::has('personal.goals.store'));
    }

    public function test_public_user_can_register_into_private_personal_workspace(): void
    {
        $response = $this->post(route('personal.register.store'), [
            'name' => 'Ahmad Personal',
            'email' => 'ahmad.personal@example.test',
            'phone' => '081234567890',
            'password' => 'RahasiaAman123',
            'password_confirmation' => 'RahasiaAman123',
            'terms' => '1',
        ]);

        $user = User::where('email','ahmad.personal@example.test')->firstOrFail();
        $workspace = Institution::findOrFail($user->institution_id);
        $this->assertSame('personal',$workspace->workspace_type);
        $this->assertSame('private',$workspace->privacy_mode);
        $this->assertSame($user->id,(int) $workspace->owner_user_id);
        $this->assertTrue($user->hasRole('personal'));
        $this->assertTrue($user->hasPermission('personal.use'));
        $this->assertDatabaseHas('personal_profiles',['user_id'=>$user->id,'institution_id'=>$workspace->id]);
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('personal.dashboard'));
        $this->get(route('personal.dashboard'))->assertOk()->assertSee('Ruang Personal');

        $this->post(route('personal.activities.store'), [
            'activity_type'=>'reflection',
            'duration_minutes'=>5,
            'self_rating'=>'steady',
            'notes'=>'Refleksi pertama.',
            'practiced_on'=>today()->toDateString(),
        ])->assertRedirect();
        $this->assertDatabaseHas('personal_practice_entries',[
            'user_id'=>$user->id,
            'activity_type'=>'reflection',
            'duration_minutes'=>5,
        ]);
    }

    public function test_personal_user_cannot_complete_another_users_goal(): void
    {
        [$userA, $profileA] = $this->personalUser('A');
        [$userB, $profileB] = $this->personalUser('B');
        $goalB = PersonalGoal::create([
            'institution_id'=>$userB->institution_id,
            'personal_profile_id'=>$profileB->id,
            'user_id'=>$userB->id,
            'title'=>'Target B',
            'metric'=>'sessions',
            'target_value'=>3,
            'starts_on'=>today(),
            'status'=>'active',
        ]);

        $this->actingAs($userA)
            ->put(route('personal.goals.complete',$goalB))
            ->assertNotFound();
        $this->assertSame('active',$goalB->fresh()->status);
        $this->assertNotSame($profileA->institution_id,$profileB->institution_id);
    }

    public function test_guidance_does_not_use_stifin_profile(): void
    {
        [$user, $profile] = $this->personalUser('Guardrail');
        $profile->student->update(['stifin_status'=>'tested','stifin_result'=>'TEST-SENSITIVE-LABEL']);

        $snapshot = app(PersonalJourneyService::class)->snapshot($user,$profile);
        $guidance = strtolower(json_encode($snapshot['guidance'], JSON_UNESCAPED_UNICODE));
        $this->assertStringNotContainsString('stifin',$guidance);
        $this->assertStringNotContainsString('test-sensitive-label',$guidance);
    }

    private function personalUser(string $suffix): array
    {
        $workspace = Institution::create([
            'name'=>'Personal '.$suffix,
            'code'=>'PRS-'.$suffix,
            'slug'=>'personal-'.strtolower($suffix),
            'workspace_type'=>'personal',
            'privacy_mode'=>'private',
            'status'=>'active',
        ]);
        $user = User::create([
            'institution_id'=>$workspace->id,
            'name'=>'User '.$suffix,
            'email'=>'user-'.strtolower($suffix).'@example.test',
            'password'=>'RahasiaAman123',
            'status'=>'active',
        ]);
        $role = Role::where('name','personal')->firstOrFail();
        $user->roles()->attach($role->id,['institution_id'=>$workspace->id,'status'=>'active']);
        $student = Student::create([
            'institution_id'=>$workspace->id,
            'student_code'=>'PERSONAL-'.$suffix,
            'full_name'=>$user->name,
            'status'=>'active',
        ]);
        $profile = PersonalProfile::create([
            'institution_id'=>$workspace->id,
            'user_id'=>$user->id,
            'student_id'=>$student->id,
            'daily_minutes'=>20,
        ]);
        $workspace->update(['owner_user_id'=>$user->id]);
        return [$user,$profile->load('student')];
    }
}
