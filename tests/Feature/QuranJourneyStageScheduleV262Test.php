<?php

namespace Tests\Feature;

use App\Http\Controllers\Teacher\QuranJourneyController;
use App\Services\QuranJourneyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class QuranJourneyStageScheduleV262Test extends TestCase
{
    use RefreshDatabase;

    public function test_stage_schedule_history_schema_is_available(): void
    {
        $this->assertTrue(Schema::hasColumn('student_marhalah_histories', 'journey_juz_number'));
        $this->assertTrue(Schema::hasColumn('student_marhalah_histories', 'stage_code'));
        $this->assertTrue(Schema::hasColumn('student_marhalah_histories', 'portion_label'));
        $this->assertTrue(Schema::hasColumn('student_marhalah_histories', 'cadence_mode'));
        $this->assertTrue(Schema::hasColumn('student_marhalah_histories', 'cadence_notes'));
    }

    public function test_quran_journey_exposes_stage_specific_schedule_update(): void
    {
        $this->assertTrue(method_exists(QuranJourneyService::class, 'updateCadence'));
        $this->assertTrue(method_exists(QuranJourneyController::class, 'updateCadence'));

        $view = file_get_contents(resource_path('views/teacher/quran-journey/student.blade.php'));
        $this->assertStringContainsString('Arahan tahap aktif', $view);
        $this->assertStringContainsString('Riwayat arahan tahap sebelumnya', $view);
        $this->assertStringContainsString("route('teacher.quran-journey.cadence.update',\$student)", $view);
    }

    public function test_new_stage_is_not_documented_as_automatically_daily(): void
    {
        $service = file_get_contents(app_path('Services/QuranJourneyService.php'));
        $this->assertStringContainsString("'cadence_mode'=>'flexible'", $service);
        $this->assertStringContainsString("'cadence_notes'=>null", $service);
    }
}
