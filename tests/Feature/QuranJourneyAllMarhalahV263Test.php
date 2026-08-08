<?php

namespace Tests\Feature;

use App\Http\Controllers\Teacher\QuranJourneyController;
use App\Services\MushafPageService;
use App\Services\QuranJourneyService;
use Tests\TestCase;

class QuranJourneyAllMarhalahV263Test extends TestCase
{
    public function test_all_marhalah_rules_are_locked_to_juz_and_portion(): void
    {
        $rules = app(QuranJourneyService::class)->stageRules();

        $this->assertSame(['ayah', 1], [$rules[30]['unit'], $rules[30]['value']]);
        $this->assertSame(['line', 3], [$rules[29]['unit'], $rules[29]['value']]);
        $this->assertSame(['line', 5], [$rules[28]['unit'], $rules[28]['value']]);
        $this->assertSame('page', $rules[27]['unit']);
        $this->assertEquals(0.5, $rules[27]['value']);
        $this->assertSame(['page', 1], [$rules[26]['unit'], $rules[26]['value']]);
        $this->assertSame(['page', 2], [$rules[1]['unit'], $rules[1]['value']]);
    }

    public function test_page_engine_and_route_handler_exist(): void
    {
        $this->assertTrue(class_exists(MushafPageService::class));
        $this->assertTrue(method_exists(MushafPageService::class, 'optionsForStage'));
        $this->assertTrue(method_exists(MushafPageService::class, 'createPagePortion'));
        $this->assertTrue(method_exists(QuranJourneyController::class, 'storePagePortion'));
    }

    public function test_teacher_view_explains_page_patterns(): void
    {
        $view = file_get_contents(resource_path('views/teacher/quran-journey/student.blade.php'));

        $this->assertStringContainsString('bagian atas slot 1–8', $view);
        $this->assertStringContainsString('bagian bawah slot 9–15', $view);
        $this->assertStringContainsString('1 halaman penuh: slot 1–15', $view);
        $this->assertStringContainsString('2 halaman berurutan: 15 + 15 slot fisik', $view);
        $this->assertStringContainsString('teacher.quran-journey.page-portions.store', $view);
    }
}
