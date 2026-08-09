<?php

namespace Tests\Feature;

use App\Models\FeatureFlag;
use App\Models\User;
use App\Services\PersonalModuleAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class UnifiedPersonalEcosystemV400Test extends TestCase
{
    use RefreshDatabase;

    public function test_v400_schema_routes_and_visible_personal_home_exist(): void
    {
        $user = $this->registerPersonal('visible');

        $this->assertTrue(Schema::hasTable('personal_check_ins'));
        $this->assertTrue(Schema::hasColumn('payment_transactions', 'verified_at'));
        $this->assertTrue(Route::has('personal.journey.index'));
        $this->assertTrue(Route::has('personal.community.index'));
        $this->assertTrue(Route::has('personal.payments.index'));
        $this->assertTrue(Route::has('admin.ecosystem.index'));

        $this->actingAs($user)->get(route('personal.dashboard'))
            ->assertOk()->assertSee('SATU RUANG QUR’AN')->assertSee('Perjalanan Saya');
    }

    public function test_personal_check_in_is_private_and_unique_per_day(): void
    {
        $user = $this->registerPersonal('checkin');
        $payload = ['energy' => 'steady', 'focus' => 'murajaah', 'intention' => 'Menjaga satu halaman'];

        $this->actingAs($user)->post(route('personal.check-in.store'), $payload)->assertRedirect();
        $this->actingAs($user)->post(route('personal.check-in.store'), $payload + ['reflection' => 'Lebih tenang'])->assertRedirect();

        $this->assertDatabaseCount('personal_check_ins', 1);
        $this->assertDatabaseHas('personal_check_ins', ['user_id' => $user->id, 'focus' => 'murajaah', 'reflection' => 'Lebih tenang']);
    }

    public function test_governed_modules_require_feature_and_enrollment(): void
    {
        $user = $this->registerPersonal('governed');
        $access = app(PersonalModuleAccessService::class);

        $this->assertFalse($access->allows($user, 'community'));
        $this->assertFalse($access->allows($user, 'payments'));

        foreach (['community', 'payments'] as $featureKey) {
            FeatureFlag::query()->updateOrCreate(
                ['institution_id' => $user->institution_id, 'feature_key' => $featureKey],
                ['enabled' => true],
            );
            Cache::forget('sullam:feature:'.$user->institution_id.':'.$featureKey);
        }
        $access->syncAssignedAccess($user, ['community', 'payments'], $user);

        $this->assertTrue($access->allows($user->fresh(), 'community'));
        $this->assertTrue($access->allows($user->fresh(), 'payments'));
    }

    private function registerPersonal(string $suffix): User
    {
        $this->post(route('personal.register.store'), [
            'name' => 'Personal '.ucfirst($suffix),
            'email' => 'personal-v400-'.$suffix.'@example.test',
            'password' => 'RahasiaAman123',
            'password_confirmation' => 'RahasiaAman123',
            'terms' => '1',
        ])->assertRedirect(route('personal.dashboard'));

        return User::query()->where('email', 'personal-v400-'.$suffix.'@example.test')->firstOrFail();
    }
}
