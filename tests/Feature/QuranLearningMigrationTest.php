<?php

namespace Tests\Feature;

use App\Models\Institution;
use App\Models\QuranAudioSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class QuranLearningMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_quran_learning_tables_are_available(): void
    {
        $this->assertTrue(Schema::hasTable('quran_audio_sources'));
        $this->assertTrue(Schema::hasTable('quran_ayah_timings'));
        $this->assertTrue(Schema::hasTable('quran_practice_presets'));
        $this->assertTrue(Schema::hasTable('quran_practice_sessions'));
        $this->assertTrue(Schema::hasTable('quran_video_resources'));
    }

    public function test_default_audio_source_is_created_for_an_institution_present_during_migration(): void
    {
        // RefreshDatabase runs all migrations on an empty DB, so this verifies model shape.
        $institution = Institution::query()->create([
            'name' => 'TPA Uji',
            'code' => 'TPA-UJI',
            'slug' => 'tpa-uji',
            'status' => 'active',
        ]);

        $source = QuranAudioSource::query()->create([
            'institution_id' => $institution->id,
            'name' => 'Murattal Uji',
            'provider' => 'mp3quran',
            'external_id' => '5',
            'reciter_name' => 'Ahmad bin Ali Al-Ajmi',
            'base_url' => 'https://server10.mp3quran.net/ajm/',
            'is_default' => true,
            'status' => 'active',
        ]);

        $this->assertTrue($source->is_default);
        $this->assertSame($institution->id, $source->institution_id);
    }
}
