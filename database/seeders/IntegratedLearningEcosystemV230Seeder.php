<?php

namespace Database\Seeders;

use App\Models\AcademyLearningPath;
use App\Models\AcademyLearningPathItem;
use App\Models\AcademyLesson;
use App\Models\AcademyModule;
use App\Models\AcademyProgram;
use App\Models\CommunitySpace;
use App\Models\FeatureFlag;
use App\Models\Institution;
use App\Models\IntegrationConnection;
use App\Models\Permission;
use App\Models\QuranPracticePreset;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class IntegratedLearningEcosystemV230Seeder extends Seeder
{
    private const SAMPLE_VIDEO = 'https://www.youtube.com/watch?v=V_dovd7ezCA';

    public function run(): void
    {
        $this->permissions();

        foreach (Institution::query()->where('status', 'active')->get() as $institution) {
            $this->features((int) $institution->id);
            $this->categorizeExistingPrograms((int) $institution->id);
            $this->seedPrograms((int) $institution->id);
            $this->seedLearningPaths((int) $institution->id);
            $this->seedExpansionScaffolds($institution);
        }
    }

    private function permissions(): void
    {
        $catalog = [
            'portfolio.view' => 'Melihat portofolio perkembangan',
            'portfolio.manage' => 'Mengelola portofolio perkembangan',
            'community.view' => 'Melihat community terbatas',
            'community.manage' => 'Mengelola dan memoderasi community',
            'insights.view' => 'Melihat insight perkembangan',
            'insights.manage' => 'Mengelola insight perkembangan',
            'integrations.manage' => 'Mengelola integrasi eksternal',
        ];

        foreach ($catalog as $name => $displayName) {
            Permission::updateOrCreate(['name' => $name], ['display_name' => $displayName]);
        }

        $rolePermissions = [
            'superadmin' => array_keys($catalog),
            'institution_admin' => array_keys($catalog),
            'head' => ['portfolio.view', 'portfolio.manage', 'community.view', 'community.manage', 'insights.view', 'insights.manage'],
            'teacher' => ['portfolio.view', 'portfolio.manage', 'community.view', 'insights.view'],
            'guardian' => ['portfolio.view', 'community.view', 'insights.view'],
            'student' => ['portfolio.view', 'community.view'],
        ];

        foreach ($rolePermissions as $roleName => $permissions) {
            $role = Role::where('name', $roleName)->first();
            if (! $role) {
                continue;
            }
            $ids = Permission::whereIn('name', $permissions)->pluck('id');
            $role->permissions()->syncWithoutDetaching($ids);
        }
    }

    private function features(int $institutionId): void
    {
        $features = [
            'core_academic' => true,
            'academy_portal' => true,
            'quran_audio' => true,
            'parent_academy' => true,
            'teacher_academy' => true,
            'stifin_learning' => true,
            'family_learning' => true,
            'learning_paths' => true,
            'academy_reflections' => true,
            'character_talent' => true,
            'student_portfolio' => true,
            'learning_insights' => true,
            'public_website' => true,
            'report_cards' => true,
            'admissions' => true,
            'api_integrations' => true,
            'community' => false,
            'ai_assist' => false,
            'payments' => false,
            'multi_branch' => false,
            'multi_institution' => false,
        ];

        foreach ($features as $key => $enabled) {
            // Hanya menetapkan nilai awal. Pilihan admin setelah go-live tidak boleh
            // ditimpa kembali pada setiap restart/redeploy.
            FeatureFlag::firstOrCreate(
                ['institution_id' => $institutionId, 'feature_key' => $key],
                ['enabled' => $enabled],
            );
        }
    }

    private function categorizeExistingPrograms(int $institutionId): void
    {
        $map = [
            'parent-academy-rumah-qurani' => ['family', 'parent'],
            'orientasi-guru-sullamul-hifz' => ['teacher', 'teacher'],
            'stifin-sebagai-informasi-pendamping' => ['personalization', 'stifin'],
            'stifin-parenting-mendampingi-tanpa-membatasi' => ['family', 'stifin-parenting'],
            'hidup-bersama-al-quran' => ['quran', 'quran-life'],
            'pendidikan-anak-adab-keteladanan' => ['parenting', 'child-education'],
        ];

        foreach ($map as $slug => [$category, $track]) {
            $program = AcademyProgram::query()
                ->where('institution_id', $institutionId)
                ->where('slug', $slug)
                ->first();

            if (! $program) {
                continue;
            }

            // Klasifikasi lama hanya dilengkapi bila belum ditentukan admin.
            $changes = [];
            if (! $program->category) $changes['category'] = $category;
            if (! $program->learning_track) $changes['learning_track'] = $track;
            if ($changes) $program->update($changes);
        }
    }

    private function seedPrograms(int $institutionId): void
    {
        $programs = [
            [
                'slug' => 'guru-sebagai-pembimbing-perjalanan',
                'title' => 'Guru sebagai Pembimbing Perjalanan',
                'audience' => 'teacher',
                'category' => 'teacher',
                'learning_track' => 'teacher-development',
                'summary' => 'Microlearning untuk menjaga pembelajaran tetap manusiawi, terarah, dan ringan secara administrasi.',
                'description' => 'Program lanjutan Teacher Academy tentang pembelajaran bertahap, personalisasi, komunikasi keluarga, observasi nyata, dan penggunaan data sebagai alat bantu keputusan.',
                'is_featured' => true,
                'sort_order' => 20,
                'modules' => [
                    ['title' => '1. Pembimbing, Bukan Pengejar Angka', 'summary' => 'Menjaga tujuan pembinaan.', 'lessons' => [
                        ['Membaca Kemajuan Tanpa Ranking', 'article', 6, "Perkembangan tidak harus diringkas menjadi posisi atau peringkat. Lihat bukti yang membantu tindakan berikutnya: kesalahan bacaan berkurang, kemampuan memanggil hafalan lama meningkat, anak lebih tenang saat dikoreksi, atau konsistensi latihan mulai terbentuk.\n\nData dipakai untuk membantu keputusan, bukan menentukan nilai manusia."],
                        ['Catatan Guru dalam 60 Detik', 'checklist', 5, "Gunakan tiga unsur sederhana: apa yang terjadi, apa yang perlu dikuatkan, dan apa langkah berikutnya.\n\nContoh: “Al-Qāri‘ah 1–5 lebih stabil. Ayat 3 masih tertukar pada bagian akhir. Ulang 5× dengan tempo pelan, lalu cek kembali pada pertemuan berikutnya.”"],
                    ]],
                    ['title' => '2. Personalisasi Berbasis Observasi', 'summary' => 'Menguji metode tanpa memberi label.', 'lessons' => [
                        ['Satu Target, Beberapa Cara Belajar', 'activity', 7, "Pilih satu target yang sama. Siapkan dua atau tiga cara: membaca berulang, mendengar murattal, menulis, gerakan, atau teach-back. Coba secukupnya dan catat respons nyata.\n\nMetode dapat berbeda, standar ketepatan bacaan tetap dijaga."],
                        ['STIFIn sebagai Hipotesis, Bukan Kesimpulan', 'article', 6, "Bila hasil STIFIn tersedia, gunakan sebagai informasi tambahan untuk mencoba pendekatan. Jangan menjadikannya alasan untuk menetapkan kemampuan, marhalah, atau masa depan anak. Bila hasil pemetaan bertentangan dengan perkembangan nyata, observasi nyata didahulukan."],
                    ]],
                ],
            ],
            [
                'slug' => 'pendidikan-anak-beradab-dan-tangguh',
                'title' => 'Pendidikan Anak — Beradab, Tangguh, dan Bertumbuh',
                'audience' => 'guardian',
                'category' => 'parenting',
                'learning_track' => 'child-education',
                'summary' => 'Contoh e-course pendidikan anak yang menghubungkan adab, komunikasi, keteladanan, kemandirian, dan pertumbuhan.',
                'description' => 'Materi keluarga yang tidak hanya membahas hafalan. Fokusnya adalah suasana rumah, hubungan orang tua-anak, adab, ketangguhan, tanggung jawab, dan kebiasaan kecil yang dijaga.',
                'is_featured' => true,
                'sort_order' => 21,
                'modules' => [
                    ['title' => '1. Rumah sebagai Lingkungan Pendidikan', 'summary' => 'Anak belajar dari suasana dan teladan.', 'lessons' => [
                        ['Anak Lebih Banyak Melihat daripada Mendengar Nasihat', 'article', 7, "Keteladanan hadir dalam kebiasaan kecil: cara orang tua berbicara, meminta maaf, menepati janji, mengelola emosi, menjaga waktu, dan memperlakukan Al-Qur’an dengan hormat.\n\nPilih satu kebiasaan keluarga yang ingin dijaga selama sepekan. Tidak perlu banyak sekaligus."],
                        ['Percakapan 10 Menit Tanpa Menggurui', 'activity', 8, "Sediakan waktu singkat untuk mendengar anak. Gunakan pertanyaan terbuka: apa yang paling menyenangkan hari ini, apa yang terasa sulit, dan bantuan seperti apa yang dibutuhkan.\n\nTujuannya bukan menyelesaikan semua masalah dalam satu percakapan, tetapi membangun rasa aman untuk bercerita."],
                    ]],
                    ['title' => '2. Adab, Tanggung Jawab, dan Kemandirian', 'summary' => 'Membangun kemampuan melalui latihan nyata.', 'lessons' => [
                        ['Memberi Tanggung Jawab yang Sesuai Usia', 'checklist', 6, "Berikan tanggung jawab kecil yang nyata: menyiapkan perlengkapan belajar, merapikan tempat, mengingat jadwal, atau memilih waktu murāja‘ah bersama orang tua.\n\nBantu bila perlu, tetapi jangan mengambil alih seluruh proses."],
                        ['Video Contoh — Pendidikan sebagai Pendampingan', 'video', 6, "Video ini digunakan sebagai contoh integrasi media pada e-course. Setelah menonton, pilih satu gagasan yang relevan dengan kondisi keluarga dan uji dalam tindakan kecil. Video bukan pengganti dialog, keteladanan, atau pendampingan langsung.", self::SAMPLE_VIDEO],
                    ]],
                ],
            ],
            [
                'slug' => 'karakter-bakat-dan-keberanian',
                'title' => 'Karakter, Bakat, dan Keberanian Bertumbuh',
                'audience' => 'all',
                'category' => 'talent',
                'learning_track' => 'character-talent',
                'summary' => 'Fondasi fase pengembangan anak melalui public speaking, kreativitas, gerak, kerja sama, dan tanggung jawab.',
                'description' => 'Program contoh untuk menunjukkan bahwa ekosistem Sullamul Ḥifẓ dapat mendokumentasikan pertumbuhan di luar hafalan tanpa mengubahnya menjadi ranking.',
                'is_featured' => false,
                'sort_order' => 22,
                'modules' => [
                    ['title' => '1. Keberanian yang Dilatih', 'summary' => 'Berani bukan berarti selalu percaya diri.', 'lessons' => [
                        ['Public Speaking sebagai Latihan Keberanian', 'activity', 7, "Minta anak menceritakan satu hal yang dipelajari selama 60–90 detik. Fokus pada keberanian memulai, menyampaikan gagasan, dan menyelesaikan cerita. Tidak perlu dibandingkan dengan anak lain."],
                        ['Kreativitas sebagai Cara Memahami', 'activity', 7, "Gunakan gambar, peta konsep, benda, atau cerita untuk mengekspresikan pemahaman. Produk akhir bukan tujuan utama; proses mencoba, menjelaskan, dan memperbaiki adalah bagian dari pertumbuhan."],
                    ]],
                    ['title' => '2. Gerak dan Kerja Sama', 'summary' => 'Belajar melalui tubuh dan kelompok.', 'lessons' => [
                        ['Olahraga untuk Disiplin dan Kerja Sama', 'article', 6, "Futsal atau aktivitas gerak dapat menjadi lingkungan untuk belajar aturan, giliran, ketekunan, mengelola kecewa, dan bekerja sama. Catatan perkembangan cukup menyoroti hal yang bermakna, bukan membuat skor karakter."],
                    ]],
                ],
            ],
        ];

        foreach ($programs as $programData) {
            $modules = $programData['modules'];
            unset($programData['modules']);

            $program = AcademyProgram::firstOrCreate(
                ['institution_id' => $institutionId, 'slug' => $programData['slug']],
                $programData + ['status' => 'published'],
            );

            // Metadata teknis boleh diperbarui; klasifikasi dan teks yang sudah diedit
            // admin tidak ditimpa pada restart/redeploy berikutnya.
            $changes = ['metadata' => array_merge((array) ($program->metadata ?? []), ['v230_example' => true])];
            if (! $program->category) $changes['category'] = $programData['category'];
            if (! $program->learning_track) $changes['learning_track'] = $programData['learning_track'];
            $program->update($changes);

            foreach ($modules as $moduleIndex => $moduleData) {
                $module = AcademyModule::firstOrCreate(
                    ['academy_program_id' => $program->id, 'title' => $moduleData['title']],
                    ['summary' => $moduleData['summary'], 'sort_order' => $moduleIndex + 1, 'status' => 'published'],
                );

                foreach ($moduleData['lessons'] as $lessonIndex => $lessonData) {
                    [$title, $type, $minutes, $body] = array_slice($lessonData, 0, 4);
                    $mediaUrl = $lessonData[4] ?? null;
                    AcademyLesson::firstOrCreate(
                        ['academy_module_id' => $module->id, 'slug' => Str::slug($title)],
                        [
                            'title' => $title,
                            'lesson_type' => $type,
                            'summary' => Str::limit(preg_replace('/\s+/', ' ', $body), 170),
                            'body' => $body,
                            'media_url' => $mediaUrl,
                            'duration_minutes' => $minutes,
                            'sort_order' => $lessonIndex + 1,
                            'requires_action' => in_array($type, ['activity', 'checklist', 'reflection'], true),
                            'status' => 'published',
                            'metadata' => ['v230_example' => true],
                        ],
                    );
                }
            }
        }
    }

    private function seedLearningPaths(int $institutionId): void
    {
        if (! Schema::hasTable('academy_learning_paths')) {
            return;
        }

        $paths = [
            [
                'slug' => 'mulai-dari-rumah',
                'title' => 'Mulai dari Rumah',
                'audience' => 'guardian',
                'category' => 'family',
                'summary' => 'Jalur singkat untuk orang tua: suasana rumah, komunikasi, murāja‘ah, dan observasi anak.',
                'is_featured' => true,
                'sort_order' => 1,
                'lesson_titles' => [
                    'Membangun Suasana Rumah yang Dekat dengan Al-Qur’an',
                    'Cara Membantu Murajaah di Rumah',
                    'Bahasa Orang Tua yang Tidak Mengunci Anak',
                    'Percakapan 10 Menit Tanpa Menggurui',
                ],
                'quran_preset' => true,
            ],
            [
                'slug' => 'guru-pembimbing-perjalanan',
                'title' => 'Guru Pembimbing Perjalanan',
                'audience' => 'teacher',
                'category' => 'teacher',
                'summary' => 'Jalur orientasi dan microlearning untuk guru Sullamul Ḥifẓ.',
                'is_featured' => true,
                'sort_order' => 2,
                'lesson_titles' => [
                    'Minimum Meaningful Documentation',
                    'Human Before Data & No Ranking Culture',
                    'Catatan Guru dalam 60 Detik',
                    'Satu Target, Beberapa Cara Belajar',
                    'Merekomendasikan Materi Parent Academy',
                ],
            ],
            [
                'slug' => 'murajaah-tenang',
                'title' => 'Murāja‘ah dengan Tenang',
                'audience' => 'all',
                'category' => 'quran',
                'summary' => 'Materi singkat lalu langsung berlatih dengan Audio Qur’an tanpa keluar dari Academy.',
                'is_featured' => true,
                'sort_order' => 3,
                'lesson_titles' => [
                    'Murāja‘ah adalah Nafas Hafalan',
                    'Menggunakan Murattal 10× dengan Bijak',
                ],
                'quran_preset' => true,
            ],
        ];

        foreach ($paths as $pathData) {
            $lessonTitles = $pathData['lesson_titles'];
            $withPreset = (bool) ($pathData['quran_preset'] ?? false);
            unset($pathData['lesson_titles'], $pathData['quran_preset']);

            $path = AcademyLearningPath::firstOrCreate(
                ['institution_id' => $institutionId, 'slug' => $pathData['slug']],
                $pathData + ['status' => 'published', 'metadata' => ['v230_example' => true]],
            );

            $order = 1;
            foreach ($lessonTitles as $title) {
                $lesson = AcademyLesson::query()
                    ->where('title', $title)
                    ->whereHas('module.program', fn ($query) => $query->where('institution_id', $institutionId))
                    ->first();
                if (! $lesson) {
                    continue;
                }

                AcademyLearningPathItem::updateOrCreate(
                    ['academy_learning_path_id' => $path->id, 'item_type' => 'lesson', 'item_id' => $lesson->id],
                    ['sort_order' => $order++, 'is_required' => true],
                );
            }

            if ($withPreset) {
                $preset = QuranPracticePreset::query()
                    ->where('institution_id', $institutionId)
                    ->where('status', 'active')
                    ->orderByDesc('is_featured')
                    ->orderBy('id')
                    ->first();
                if ($preset) {
                    AcademyLearningPathItem::updateOrCreate(
                        ['academy_learning_path_id' => $path->id, 'item_type' => 'quran_preset', 'item_id' => $preset->id],
                        ['title_override' => 'Latihan Audio Qur’an', 'instruction' => 'Lanjutkan dengan latihan audio di Academy.', 'sort_order' => $order, 'is_required' => false],
                    );
                }
            }
        }
    }

    private function seedExpansionScaffolds(Institution $institution): void
    {
        if (Schema::hasTable('community_spaces')) {
            CommunitySpace::firstOrCreate(
                ['institution_id' => $institution->id, 'space_type' => 'institution', 'name' => 'Community TPA Al-Insyirah'],
                [
                    'description' => 'Ruang komunitas terbatas untuk agenda, materi bersama, dan dokumentasi setelah moderasi diaktifkan.',
                    'moderation_mode' => 'approval',
                    'status' => 'draft',
                    'settings' => ['child_media_default' => 'deny', 'comments_enabled' => false],
                ],
            );
        }

        if (Schema::hasTable('integration_connections')) {
            foreach ([
                'whatsapp' => 'WhatsApp / notifikasi keluarga',
                'email' => 'Email transaksional',
                'object_storage' => 'Object Storage media',
                'payment_gateway' => 'Payment Gateway',
            ] as $provider => $displayName) {
                IntegrationConnection::firstOrCreate(
                    ['institution_id' => $institution->id, 'provider' => $provider],
                    ['display_name' => $displayName, 'status' => 'disabled', 'configuration' => ['prepared_by' => 'v2.3.0']],
                );
            }
        }
    }
}
