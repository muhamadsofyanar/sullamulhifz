<?php

namespace Tests\Feature;

/** @phase 5.1 SaaS Production Readiness */

use App\Models\User;
use App\Services\SaasReadinessService;
use Database\Seeders\ProductionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SaasReadinessV510Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        putenv('INITIAL_ADMIN_PASSWORD=TestAdmin2026Secure');
        putenv('SEED_INITIAL_TPA_DATA=false');
        $this->seed(ProductionSeeder::class);
    }

    public function test_operational_schema_and_critical_checks_are_available(): void
    {
        $this->assertTrue(Schema::hasTable('operational_check_runs'));
        $service = app(SaasReadinessService::class);
        $summary = $service->summary($service->checks());
        $this->assertTrue($summary['critical_ready']);
    }

    public function test_admin_can_persist_operational_snapshot(): void
    {
        $admin = User::query()->whereHas('roles', fn ($query) => $query->where('roles.name', 'institution_admin'))->firstOrFail();
        $service = app(SaasReadinessService::class);
        $service->persist($admin);
        $this->assertDatabaseHas('operational_check_runs', ['created_by_user_id' => $admin->id, 'check_key' => 'database_schema']);
    }
}
