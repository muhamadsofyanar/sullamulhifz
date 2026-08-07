<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\LaunchCheck;
use Illuminate\Database\Seeder;

class TahfizhLearningEngineV250Seeder extends Seeder
{
    public function run(): void
    {
        $checks = [
            ['phase3_teacher_flow', 'Roadmap Fase 3', 'Guru dapat menjalankan target → persiapan → setoran → penguatan → Murāja‘ah tanpa alur terputus'],
            ['phase3_guardian_flow', 'Roadmap Fase 3', 'Wali melihat arahan keluarga, target, setoran, dan jadwal penjagaan anak tanpa data santri lain'],
            ['phase3_talaqqi_tasmi', 'Roadmap Fase 3', 'Talaqqi dan tasmi‘ telah diuji sebagai cara belajar/setoran, termasuk histori dan tindak lanjut'],
            ['phase3_review_followup', 'Roadmap Fase 3', 'Jadwal Murāja‘ah, fokus koreksi, penyelesaian, dan penjadwalan ulang telah diuji end-to-end'],
            ['phase3_mobile_workflow', 'Roadmap Fase 3', 'Form Tahsīn/Tahfizh/Murāja‘ah dan dashboard perjalanan Tahfizh nyaman digunakan dari ponsel'],
        ];

        Institution::query()->where('status', 'active')->each(function (Institution $institution) use ($checks): void {
            foreach ($checks as [$key, $category, $label]) {
                LaunchCheck::query()->firstOrCreate(
                    ['institution_id' => $institution->id, 'check_key' => $key],
                    ['category' => $category, 'label' => $label, 'status' => 'pending'],
                );
            }
        });
    }
}
