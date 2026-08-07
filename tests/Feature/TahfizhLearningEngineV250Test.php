<?php

namespace Tests\Feature;

use App\Services\TahfizhLearningService;
use App\Services\TahfizhProgressService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TahfizhLearningEngineV250Test extends TestCase
{
    use RefreshDatabase;

    public function test_phase_three_tables_and_columns_are_available(): void
    {
        $this->assertTrue(Schema::hasTable('tahfizh_learning_cycles'));
        $this->assertTrue(Schema::hasTable('memorization_review_plans'));
        $this->assertTrue(Schema::hasTable('quran_learning_error_items'));
        $this->assertTrue(Schema::hasColumn('memorization_records', 'delivery_mode'));
        $this->assertTrue(Schema::hasColumn('memorization_records', 'learning_cycle_id'));
        $this->assertTrue(Schema::hasColumn('memorization_records', 'next_review_date'));
        $this->assertTrue(Schema::hasColumn('murajaah_records', 'review_plan_id'));
        $this->assertTrue(Schema::hasColumn('murajaah_records', 'learning_cycle_id'));
    }

    public function test_phase_three_services_are_resolvable(): void
    {
        $this->assertInstanceOf(TahfizhLearningService::class, app(TahfizhLearningService::class));
        $this->assertInstanceOf(TahfizhProgressService::class, app(TahfizhProgressService::class));
    }
    public function test_tahfizh_student_view_does_not_shadow_laravel_validation_errors_bag(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Teacher/TahfizhController.php'));
        $view = file_get_contents(resource_path('views/teacher/tahfizh/student.blade.php'));

        $this->assertStringNotContainsString("'errors' => QuranLearningErrorItem::query()", $controller);
        $this->assertStringContainsString("'correctionItems' => QuranLearningErrorItem::query()", $controller);
        $this->assertStringContainsString('$correctionItems->whereNull', $view);
        $this->assertStringNotContainsString('@forelse($errors as $error)', $view);
    }

}
