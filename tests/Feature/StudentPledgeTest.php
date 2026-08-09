<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use Database\Seeders\PlatformFoundationV210Seeder;
use Database\Seeders\ProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPledgeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('INITIAL_ADMIN_PASSWORD=TestAdmin2026Secure');
        putenv('SEED_INITIAL_TPA_DATA=false');
    }

    public function test_public_pledge_uses_default_content(): void
    {
        $this->get('https://sullamulhifz.or.id/ikrar-santri')
            ->assertOk()
            ->assertSee('Taat kepada Allah')
            ->assertSee('Mencintai, membaca, menghafal, dan menjaga Al-Qur’an.');
    }

    public function test_admin_can_update_student_pledge(): void
    {
        $this->seed(ProductionSeeder::class);
        $this->seed(PlatformFoundationV210Seeder::class);

        $admin = User::query()->firstOrFail();
        $admin->update(['must_change_password' => false]);

        $payload = config('student_pledge');
        $payload['closing'] = 'Insya Allah kami menjaga ikrar ini bersama.';

        $this->actingAs($admin)
            ->put('/admin/ikrar-santri', $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('system_settings', [
            'institution_id' => $admin->institution_id,
            'key' => 'student_pledge',
        ]);

        $stored = SystemSetting::query()
            ->where('institution_id', $admin->institution_id)
            ->where('key', 'student_pledge')
            ->firstOrFail();

        $this->assertStringContainsString('menjaga ikrar ini bersama', $stored->value);
    }
}
