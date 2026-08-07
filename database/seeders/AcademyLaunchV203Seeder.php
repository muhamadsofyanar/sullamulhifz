<?php

namespace Database\Seeders;

use App\Models\AcademyLesson;
use App\Models\AcademyModule;
use App\Models\AcademyProgram;
use App\Models\Institution;
use Illuminate\Database\Seeder;

class AcademyLaunchV203Seeder extends Seeder
{
    public function run(): void
    {
        foreach (Institution::query()->where('status', 'active')->get() as $institution) {
            $program = AcademyProgram::query()
                ->where('institution_id', $institution->id)
                ->where('slug', 'parent-academy-rumah-qurani')
                ->first();

            if (! $program) {
                continue;
            }

            $module = AcademyModule::query()
                ->where('academy_program_id', $program->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->first();

            if (! $module) {
                continue;
            }

            AcademyLesson::updateOrCreate(
                [
                    'academy_module_id' => $module->id,
                    'slug' => 'contoh-video-academy-shorts',
                ],
                [
                    'title' => 'Contoh Video Academy — Materi Pendamping',
                    'lesson_type' => 'video',
                    'summary' => 'Contoh integrasi video vertikal YouTube Shorts di dalam Academy. Judul, deskripsi, dan video dapat diganti dari menu Kelola Academy.',
                    'body' => "Video ini dipasang sebagai contoh tampilan LMS Academy.\n\nGunakan video singkat sebagai pengantar, lalu lanjutkan dengan satu pesan inti atau aktivitas keluarga yang jelas. Materi Academy sebaiknya membantu orang tua memahami langkah praktis, bukan menambah beban belajar.",
                    'media_url' => 'https://www.youtube.com/shorts/x6AVimGaykM',
                    'duration_minutes' => 2,
                    'sort_order' => 3,
                    'requires_action' => false,
                    'status' => 'published',
                ]
            );
        }
    }
}
