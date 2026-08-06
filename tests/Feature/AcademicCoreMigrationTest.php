<?php

namespace Tests\Feature;

use App\Models\QuranRubu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AcademicCoreMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_academic_core_tables_and_juz_30_rubus_are_available(): void
    {
        $this->assertTrue(Schema::hasTable('memorization_targets'));
        $this->assertTrue(Schema::hasTable('learning_observations'));
        $this->assertSame(8, QuranRubu::where('juz_number', 30)->where('status', 'active')->count());
    }
}
