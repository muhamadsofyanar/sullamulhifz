<?php

namespace Tests\Feature;

/** @phase 5.3 Mobile, Offline & Global */

use App\Models\User;
use Database\Seeders\ProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class MobileGlobalV530Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('INITIAL_ADMIN_PASSWORD=TestAdmin2026Secure');
        putenv('SEED_INITIAL_TPA_DATA=false');
        $this->seed(ProductionSeeder::class);
    }

    public function test_manifest_and_service_worker_keep_private_data_out_of_offline_cache(): void
    {
        $manifest = json_decode((string) file_get_contents(public_path('manifest.webmanifest')), true);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('id', $manifest['lang']);
        $worker = (string) file_get_contents(public_path('service-worker.js'));
        $this->assertStringContainsString("url.pathname.startsWith('/media/')", $worker);
        $this->assertStringContainsString("url.pathname.startsWith('/api/')", $worker);
        $this->assertStringContainsString("cache: 'no-store'", $worker);
    }

    public function test_user_can_store_language_timezone_and_pwa_preferences(): void
    {
        $user = $this->registerPersonal('global-pref');
        $this->actingAs($user)->get(route('preferences.edit'))->assertOk()->assertSee('Perangkat, bahasa, dan zona waktu');
        $this->actingAs($user)->put(route('preferences.update'), [
            'locale' => 'ar',
            'timezone' => 'Asia/Riyadh',
            'pwa_enabled' => '1',
            'email_notifications' => '1',
        ])->assertRedirect();
        $this->assertDatabaseHas('user_preferences', ['user_id' => $user->id, 'locale' => 'ar', 'timezone' => 'Asia/Riyadh', 'pwa_enabled' => true]);
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
