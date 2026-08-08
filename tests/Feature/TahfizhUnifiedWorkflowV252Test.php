<?php

namespace Tests\Feature;

use App\Http\Controllers\Teacher\TahfizhController;
use Tests\TestCase;

class TahfizhUnifiedWorkflowV252Test extends TestCase
{
    public function test_individual_tahfizh_controller_exposes_unified_recording_actions(): void
    {
        $this->assertTrue(method_exists(TahfizhController::class, 'storeMemorization'));
        $this->assertTrue(method_exists(TahfizhController::class, 'storeMurajaah'));
    }

    public function test_student_journey_contains_unified_memorization_and_murajaah_forms(): void
    {
        $view = file_get_contents(resource_path('views/teacher/tahfizh/student.blade.php'));

        $this->assertStringContainsString('ALUR TERPADU', $view);
        $this->assertStringContainsString("route('teacher.tahfizh.memorization.store',\$student)", $view);
        $this->assertStringContainsString("route('teacher.tahfizh.murajaah.store',\$student)", $view);
        $this->assertStringContainsString('Catat Setoran Tahfizh', $view);
        $this->assertStringContainsString('Catat Murāja‘ah', $view);
        $this->assertStringContainsString('journey-review-plan', $view);
    }

    public function test_unified_workflow_keeps_laravel_errors_bag_reserved(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Teacher/TahfizhController.php'));
        $view = file_get_contents(resource_path('views/teacher/tahfizh/student.blade.php'));

        $this->assertStringNotContainsString("'errors' => QuranLearningErrorItem::query()", $controller);
        $this->assertStringNotContainsString('@forelse($errors as $error)', $view);
    }
}
