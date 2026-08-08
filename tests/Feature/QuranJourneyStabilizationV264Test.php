<?php

namespace Tests\Feature;

use App\Models\MarhalahType;
use App\Models\QuranJourneyProfile;
use App\Models\Student;
use App\Models\User;
use App\Services\QuranJourneyService;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\ProductionSeeder;
use Database\Seeders\QuranJourneyV260Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuranJourneyStabilizationV264Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        putenv('INITIAL_ADMIN_PASSWORD=TestAdmin2026Secure');
        putenv('DEMO_PASSWORD=TestDemo2026Secure');
        putenv('SEED_INITIAL_TPA_DATA=false');
        putenv('SEED_DEMO_DATA=false');

        $this->seed(ProductionSeeder::class);
        $this->seed(DemoDataSeeder::class);
        $this->seed(QuranJourneyV260Seeder::class);
    }

    public function test_teacher_can_open_quran_journey_detail_for_every_marhalah_stage(): void
    {
        $teacher = User::query()->where('email', 'guru.demo@sullamulhifz.or.id')->firstOrFail();
        $student = Student::query()->where('student_code', 'DEMO-001')->firstOrFail();
        $service = app(QuranJourneyService::class);

        foreach ([30, 29, 28, 27, 26, 1] as $juz) {
            $rule = $service->ruleForJuz($juz);
            $marhalah = MarhalahType::query()->where('code', $rule['code'])->firstOrFail();

            QuranJourneyProfile::query()->updateOrCreate(
                ['institution_id' => $student->institution_id, 'student_id' => $student->id],
                [
                    'current_marhalah_type_id' => $marhalah->id,
                    'current_juz_number' => $juz,
                    'stage_code' => $rule['stage'],
                    'cadence_mode' => 'flexible',
                    'started_at' => now(),
                    'updated_by_teacher_id' => $teacher->teacher->id,
                    'status' => 'active',
                ],
            );

            $this->actingAs($teacher)
                ->get(route('teacher.quran-journey.student', $student))
                ->assertOk()
                ->assertSee($student->full_name)
                ->assertSee($rule['name']);
        }
    }

    public function test_teacher_view_has_no_inline_directive_after_boundary_juz_text(): void
    {
        $view = file_get_contents(resource_path('views/teacher/quran-journey/student.blade.php'));

        $this->assertStringNotContainsString('Juz@endif', $view);
        $this->assertStringNotContainsString('lain.@endif', $view);
    }

    public function test_teacher_get_path_does_not_call_mushaf_network_sync_directly(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/Teacher/QuranJourneyController.php'));
        $studentMethod = strstr($controller, 'public function student(', false);
        $studentMethod = strstr((string) $studentMethod, 'public function initialize(', true);

        $this->assertIsString($studentMethod);
        $this->assertStringNotContainsString('->syncPage(', $studentMethod);
    }
}
