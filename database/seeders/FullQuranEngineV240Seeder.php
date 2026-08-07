<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\LaunchCheck;
use Illuminate\Database\Seeder;

class FullQuranEngineV240Seeder extends Seeder
{
    public function run(): void
    {
        $checks = [
            ['phase2_mushaf_desktop_mobile', 'Roadmap Fase 2', 'Mushaf dan player Full Qur’an telah diuji di desktop dan ponsel'],
            ['phase2_audio_husary_minshawi', 'Roadmap Fase 2', 'Al-Husary dan Al-Minshawi dapat diputar pada surah awal, tengah, dan akhir Al-Qur’an'],
            ['phase2_navigation_repeat', 'Roadmap Fase 2', 'Navigasi juz/surah/halaman/rubu‘ serta repeat ayat/rentang telah diuji'],
            ['phase2_progress_resume', 'Roadmap Fase 2', 'Bookmark, riwayat baca, dan lanjutkan terakhir dibaca telah diuji'],

            ['phase4_marhalah_flow', 'Roadmap Fase 4', 'Alur keputusan marhalah naik/tetap/turun telah diuji dengan bukti perkembangan'],
            ['phase4_milestone_retention', 'Roadmap Fase 4', 'Milestone dan pemeriksaan penjagaan surah/rubu‘/juz telah diuji'],
            ['phase5_lms_resume', 'Roadmap Fase 5', 'Resume learning, prerequisite, progress dan penyelesaian LMS telah diuji'],
            ['phase5_multimedia_learning', 'Roadmap Fase 5', 'Video, audio, artikel, aktivitas/worksheet dan refleksi LMS telah diuji'],
            ['phase6_parent_teacher_flow', 'Roadmap Fase 6', 'Alur Parent Academy dan Teacher Academy telah diuji dengan pengguna nyata'],
            ['phase6_stifin_guardrail', 'Roadmap Fase 6', 'Materi STIFIn telah ditinjau agar tetap proporsional dan tidak menjadi label anak'],
            ['phase7_personalization_evidence', 'Roadmap Fase 7', 'Rekomendasi personal menggunakan observasi/perkembangan nyata sebagai bukti utama'],
            ['phase7_teacher_override', 'Roadmap Fase 7', 'Guru dapat meninjau, mengubah, atau menolak rekomendasi personalisasi'],
            ['phase8_portfolio_flow', 'Roadmap Fase 8', 'Portofolio santri dapat dilanjutkan lintas tahun ajaran tanpa ranking'],
            ['phase8_character_talent_flow', 'Roadmap Fase 8', 'Program karakter dan bakat memiliki peserta, pengampu, kegiatan, dokumentasi dan tindak lanjut'],
            ['phase9_insight_accuracy', 'Roadmap Fase 9', 'Insight/reminder telah diuji akurasi dan tidak menghasilkan klaim di luar data'],
            ['phase9_ai_human_review', 'Roadmap Fase 9', 'Semua AI Assist membutuhkan human review dan memiliki audit sebelum dipakai'],
            ['phase10_tenant_isolation', 'Roadmap Fase 10', 'Tenant isolation telah diuji lintas dua lembaga/cabang tanpa kebocoran data'],
            ['phase10_integrations', 'Roadmap Fase 10', 'Integrasi API/email/WhatsApp/object storage/payment yang diaktifkan telah diuji end-to-end'],
            ['phase10_scale_restore', 'Roadmap Fase 10', 'Uji beban, backup, dan restore lintas tenant telah lulus sebelum ekspansi'],
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
