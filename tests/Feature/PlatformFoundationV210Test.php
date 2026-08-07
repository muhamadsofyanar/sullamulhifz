<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\FeatureFlag;
use App\Models\Institution;
use Database\Seeders\PlatformFoundationV210Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PlatformFoundationV210Test extends TestCase
{
    use RefreshDatabase;

    public function test_foundation_tables_are_available(): void
    {
        foreach (['branches', 'academic_periods', 'media_assets', 'media_links', 'announcement_targets', 'friday_session_targets', 'student_marhalah_histories', 'account_invitations', 'feature_flags'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Tabel {$table} belum tersedia.");
        }
    }

    public function test_foundation_seeder_is_idempotent(): void
    {
        $institution = Institution::create([
            'name' => 'TPA Uji Fondasi',
            'code' => 'UJI-FONDASI',
            'slug' => 'uji-fondasi',
            'status' => 'active',
        ]);
        AcademicYear::create([
            'institution_id' => $institution->id,
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->seed(PlatformFoundationV210Seeder::class);
        $this->seed(PlatformFoundationV210Seeder::class);

        $this->assertDatabaseCount('branches', 1);
        $this->assertDatabaseCount('academic_periods', 1);
        $this->assertTrue((bool) FeatureFlag::where('institution_id', $institution->id)->where('feature_key', 'quran_audio')->value('enabled'));
    }

    public function test_service_worker_only_caches_explicit_static_assets(): void
    {
        $script = file_get_contents(public_path('service-worker.js'));

        $this->assertStringContainsString("url.pathname.startsWith('/media/')", $script);
        $this->assertStringContainsString('STATIC_ASSETS.has(url.pathname)', $script);
        $this->assertStringNotContainsString('cache.put(event.request', $script);
    }
}
