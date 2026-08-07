<?php

namespace Tests\Feature;

use App\Models\AcademyLesson;
use App\Models\AcademyProgram;
use App\Models\Institution;
use Database\Seeders\AcademyLaunchV200Seeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AcademyV200Test extends TestCase
{
    use RefreshDatabase;

    public function test_academy_tables_are_available(): void
    {
        $this->assertTrue(Schema::hasTable('academy_programs'));
        $this->assertTrue(Schema::hasTable('academy_modules'));
        $this->assertTrue(Schema::hasTable('academy_lessons'));
        $this->assertTrue(Schema::hasTable('academy_lesson_progress'));
        $this->assertTrue(Schema::hasTable('academy_recommendations'));
    }

    public function test_launch_seeder_creates_parent_and_teacher_academy(): void
    {
        $institution = Institution::query()->create([
            'name' => 'TPA Uji Academy',
            'code' => 'TPA-ACADEMY',
            'slug' => 'tpa-academy',
            'status' => 'active',
        ]);

        $this->seed(AcademyLaunchV200Seeder::class);

        $parent = AcademyProgram::query()
            ->where('institution_id', $institution->id)
            ->where('slug', 'parent-academy-rumah-qurani')
            ->firstOrFail();
        $teacher = AcademyProgram::query()
            ->where('institution_id', $institution->id)
            ->where('slug', 'orientasi-guru-sullamul-hifz')
            ->firstOrFail();

        $this->assertSame('published', $parent->status);
        $this->assertSame('guardian', $parent->audience);
        $this->assertSame('teacher', $teacher->audience);
        $this->assertGreaterThanOrEqual(10, AcademyLesson::query()->whereHas('module', fn ($q) => $q->where('academy_program_id', $parent->id))->count());
        $this->assertGreaterThanOrEqual(5, AcademyLesson::query()->whereHas('module', fn ($q) => $q->where('academy_program_id', $teacher->id))->count());
    }
}
