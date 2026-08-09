<?php

namespace Tests\Feature;

/** @phase 4.4.1 Blade Communication Template Hotfix */

use App\Models\CommunicationDelivery;
use App\Models\FeatureFlag;
use App\Models\Institution;
use App\Models\IntegrationConnection;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Communication\CommunicationService;
use Database\Seeders\CommunicationV410Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CommunicationCenterV410Test extends TestCase
{
    use RefreshDatabase;

    public function test_schema_routes_and_admin_center_are_available(): void
    {
        [$institution, $admin] = $this->admin();
        $this->seed(CommunicationV410Seeder::class);
        FeatureFlag::create(['institution_id' => $institution->id, 'feature_key' => 'api_integrations', 'enabled' => true]);

        $this->assertTrue(Schema::hasTable('communication_templates'));
        $this->assertTrue(Schema::hasTable('communication_deliveries'));
        $this->assertTrue(Route::has('admin.communications.index'));
        $this->assertTrue(Route::has('api.webhooks.communications.whatsapp'));

        $this->actingAs($admin)->get(route('admin.communications.index'))
            ->assertOk()
            ->assertSee('WhatsApp & Email', false)
            ->assertSee('Template notifikasi')
            ->assertSee('{{recipient_name}}', false);
    }

    public function test_starsender_driver_tracks_success_without_storing_api_key(): void
    {
        [$institution, $admin] = $this->admin();
        $this->seed(CommunicationV410Seeder::class);
        config([
            'communications.dispatch_mode' => 'sync',
            'communications.whatsapp.starsender.api_key' => 'secret-test-key',
            'communications.whatsapp.starsender.base_url' => 'https://api.starsender.online',
        ]);
        Http::fake([
            'api.starsender.online/*' => Http::response(['success' => true, 'data' => ['id' => 'wa-123']], 200),
        ]);

        $connection = IntegrationConnection::where('institution_id', $institution->id)->where('provider', 'whatsapp')->firstOrFail();
        $connection->update(['status' => 'active', 'configuration' => ['driver' => 'starsender', 'events' => ['liaison' => true]]]);

        $delivery = app(CommunicationService::class)->send(
            $institution->id,
            'whatsapp',
            '081234567890',
            'Pesan uji',
            ['event_key' => 'connection_test', 'created_by_user_id' => $admin->id],
        );

        $this->assertSame('sent', $delivery->status);
        $this->assertSame('6281234567890', $delivery->recipient_address);
        $this->assertSame('wa-123', $delivery->provider_message_id);
        $this->assertNotSame('Pesan uji', DB::table('communication_deliveries')->where('id', $delivery->id)->value('content'));
        $this->assertSame('Pesan uji', $delivery->content);
        $this->assertStringNotContainsString('secret-test-key', json_encode($connection->fresh()->configuration));
        Http::assertSent(fn ($request) => $request->url() === 'https://api.starsender.online/api/send'
            && $request->hasHeader('Authorization', 'secret-test-key')
            && $request['to'] === '6281234567890'
            && $request['body'] === 'Pesan uji');
    }

    public function test_whatsapp_webhook_is_authenticated_and_idempotent(): void
    {
        [$institution] = $this->admin();
        $this->seed(CommunicationV410Seeder::class);
        config(['communications.webhook_secret' => 'webhook-secret-test']);
        $connection = IntegrationConnection::where('institution_id', $institution->id)->where('provider', 'whatsapp')->firstOrFail();
        $connection->update(['status' => 'active', 'configuration' => ['driver' => 'starsender']]);
        $url = route('api.webhooks.communications.whatsapp', $connection);
        $payload = ['id' => 'incoming-1', 'from' => '628111111111', 'message' => 'Assalamu’alaikum', 'timestamp' => 1786255200];

        $this->postJson($url, $payload)->assertUnauthorized();
        $this->withHeader('X-Sullam-Webhook-Token', 'webhook-secret-test')->postJson($url, $payload)->assertOk()->assertJson(['ok' => true, 'duplicate' => false]);
        $this->withHeader('X-Sullam-Webhook-Token', 'webhook-secret-test')->postJson($url, $payload)->assertOk()->assertJson(['ok' => true, 'duplicate' => true]);

        $this->assertDatabaseCount('communication_deliveries', 1);
        $delivery = CommunicationDelivery::firstOrFail();
        $this->assertSame('inbound', $delivery->direction);
        $this->assertSame('Assalamu’alaikum', $delivery->content);
    }

    /** @return array{Institution,User} */
    private function admin(): array
    {
        $institution = Institution::create([
            'name' => 'TPA Komunikasi', 'code' => 'TPA-COMM', 'slug' => 'tpa-comm', 'status' => 'active',
        ]);
        $role = Role::create(['name' => 'institution_admin', 'display_name' => 'Admin Lembaga', 'scope' => 'institution']);
        $permission = Permission::create(['name' => 'integrations.manage', 'display_name' => 'Mengelola integrasi eksternal']);
        $role->permissions()->attach($permission->id);
        $admin = User::create([
            'institution_id' => $institution->id,
            'name' => 'Admin Komunikasi',
            'email' => 'admin-communication@example.test',
            'password' => 'RahasiaAman123',
            'status' => 'active',
            'must_change_password' => false,
        ]);
        $admin->roles()->attach($role->id, ['institution_id' => $institution->id, 'status' => 'active']);

        return [$institution, $admin];
    }
}
