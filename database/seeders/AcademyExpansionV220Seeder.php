<?php

namespace Database\Seeders;

use App\Models\AcademyLesson;
use App\Models\AcademyModule;
use App\Models\AcademyProgram;
use App\Models\Institution;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AcademyExpansionV220Seeder extends Seeder
{
    private const SAMPLE_VIDEO = 'https://www.youtube.com/watch?v=V_dovd7ezCA';

    public function run(): void
    {
        foreach (Institution::query()->where('status', 'active')->get() as $institution) {
            // Gunakan satu video contoh yang diberikan untuk seluruh instalasi. Seeder ini
            // juga mengganti video demo lama sehingga Academy tidak memiliki banyak sumber
            // contoh yang membingungkan pengelola.
            $legacyVideo = AcademyLesson::query()
                ->whereHas('module.program', fn ($q) => $q->where('institution_id', $institution->id))
                ->where('slug', 'contoh-video-academy-shorts')
                ->first();
            if ($legacyVideo && in_array(trim((string) $legacyVideo->media_url), ['', 'https://www.youtube.com/shorts/x6AVimGaykM'], true)) {
                $legacyVideo->update([
                    'title' => 'Contoh Video Academy — Materi Pendamping',
                    'summary' => 'Contoh teknis integrasi video YouTube di Academy. Admin dapat mengganti judul, narasi, dan sumber video melalui Kelola Academy.',
                    'body' => "Video ini digunakan sebagai contoh tampilan materi berbasis video.\n\nGunakan video sebagai pengantar, bukan sebagai pengganti arahan guru atau isi modul. Setelah video, berikan satu pesan inti atau aktivitas yang jelas agar materi tetap bermakna dan tidak sekadar menjadi tontonan.",
                    'media_url' => self::SAMPLE_VIDEO,
                    'duration_minutes' => 6,
                ]);
            }

            $programs = [
                [
                    'slug' => 'stifin-sebagai-informasi-pendamping',
                    'title' => 'STIFIn sebagai Informasi Pendamping',
                    'audience' => 'all',
                    'summary' => 'Memahami posisi STIFIn secara proporsional: sebagai informasi tambahan dan hipotesis pendampingan, bukan label atau penentu nilai anak.',
                    'description' => 'Program pengantar untuk guru dan orang tua agar hasil STIFIn, bila tersedia, digunakan bersama observasi nyata, rekam perkembangan, komunikasi keluarga, dan respons anak terhadap metode belajar.',
                    'is_featured' => true,
                    'sort_order' => 3,
                    'modules' => [
                        [
                            'title' => '1. Posisi STIFIn dalam Sullamul Ḥifẓ',
                            'summary' => 'Menempatkan STIFIn di bawah filosofi Human Before Data.',
                            'lessons' => [
                                ['Memahami STIFIn Tanpa Menjadikannya Label','article',7,"Dalam Sullamul Ḥifẓ, STIFIn bukan tujuan utama sistem dan bukan penentu marhalah, kecerdasan, potensi, atau nilai manusia. Jika hasil tes tersedia, ia dapat menjadi informasi tambahan untuk memulai percakapan tentang cara mendampingi anak.\n\nObservasi nyata tetap didahulukan. Perhatikan bagaimana anak merespons koreksi, ritme latihan, cara menerima instruksi, kebiasaan di rumah, dan perkembangan yang benar-benar terlihat."],
                                ['Yang Boleh dan Tidak Boleh Disimpulkan','checklist',6,"Gunakan hasil sebagai hipotesis, bukan vonis.\n\nBoleh:\n• mencoba cara komunikasi yang berbeda;\n• menawarkan metode belajar alternatif;\n• mencatat respons nyata anak;\n• mendiskusikan hasil bersama guru dan orang tua.\n\nHindari:\n• menentukan kemampuan hafalan hanya dari tipe;\n• menyebut anak tidak cocok belajar Al-Qur’an;\n• menjadikan tipe sebagai alasan menghapus standar bacaan;\n• membandingkan martabat atau kecerdasan antar-anak."],
                            ],
                        ],
                        [
                            'title' => '2. Dari Hipotesis ke Observasi',
                            'summary' => 'Menguji strategi melalui perkembangan nyata.',
                            'lessons' => [
                                ['Eksperimen Belajar Kecil selama 7 Hari','activity',8,"Pilih satu kebutuhan nyata, misalnya anak sulit memulai murāja‘ah. Coba satu perubahan kecil selama tujuh hari: waktu berbeda, instruksi lebih singkat, penggunaan audio, menulis, gerakan, atau teach-back.\n\nCatat dua hal saja: respons anak dan apakah kualitas proses membaik. Jika strategi tidak membantu, ganti pendekatan. Personalisasi adalah proses belajar tentang manusia, bukan membatasi manusia."],
                                ['Contoh Video Pendamping — Personalisasi yang Tetap Manusiawi','video',6,"Video ini ditempatkan sebagai contoh teknis materi video Academy. Gunakan bagian video sebagai pemantik diskusi, lalu kembali kepada prinsip utama: data membantu memahami, bukan mendefinisikan anak.\n\nPertanyaan setelah menonton: “Apa satu hal yang perlu kita amati langsung sebelum mengambil kesimpulan tentang cara belajar anak?”", self::SAMPLE_VIDEO],
                            ],
                        ],
                    ],
                ],
                [
                    'slug' => 'stifin-parenting-mendampingi-tanpa-membatasi',
                    'title' => 'STIFIn Parenting — Mendampingi Tanpa Membatasi',
                    'audience' => 'guardian',
                    'summary' => 'Menggunakan informasi tentang kecenderungan anak untuk memperbaiki komunikasi keluarga tanpa mengubah tipe menjadi cap permanen.',
                    'description' => 'E-course parenting yang menempatkan hubungan, observasi, dan perkembangan nyata di atas asumsi. Orang tua belajar mencoba strategi, mengevaluasi respons, dan berkoordinasi dengan guru.',
                    'is_featured' => false,
                    'sort_order' => 4,
                    'modules' => [
                        [
                            'title' => '1. Kenali Anak Sebelum Menilai',
                            'summary' => 'Mulai dari perilaku dan kebutuhan yang benar-benar terlihat.',
                            'lessons' => [
                                ['Membedakan Kecenderungan, Kebiasaan, dan Kondisi Hari Ini','article',7,"Anak dapat menunjukkan respons berbeda karena lelah, lapar, perubahan jadwal, suasana hati, pengalaman belajar, atau cara instruksi diberikan. Jangan semua perilaku langsung dikaitkan dengan satu tipe.\n\nTanyakan: apa yang terjadi hari ini, apa yang berulang, dan apa yang berubah ketika cara pendampingan diubah?"],
                                ['Bahasa Orang Tua yang Tidak Mengunci Anak','checklist',6,"Ganti kalimat yang mengunci dengan bahasa observasi.\n\nHindari: “Kamu memang tipe yang susah fokus.”\nGunakan: “Tadi fokusmu mulai turun setelah sepuluh menit. Kita coba sesi lebih pendek.”\n\nHindari: “Kamu bukan anak audio.”\nGunakan: “Kita sudah mencoba mendengar tiga kali. Besok kita coba menulis dan lihat mana yang lebih membantu.”"],
                            ],
                        ],
                        [
                            'title' => '2. Kolaborasi dengan Guru',
                            'summary' => 'Menyatukan pengamatan rumah dan kelas.',
                            'lessons' => [
                                ['Membawa Observasi ke Buku Penghubung','activity',6,"Kirim informasi yang spesifik dan berguna. Contoh: “Tiga hari ini Ananda lebih mudah mengulang setelah mendengar murattal dua kali, tetapi cepat lelah jika lebih dari 12 menit. Apakah target pekan ini perlu dibagi menjadi dua sesi?”\n\nInformasi seperti ini lebih membantu guru daripada label umum tentang tipe anak."],
                                ['Standar Sama, Beban Dapat Berbeda','article',6,"Personalisasi tidak berarti menghapus standar ketepatan bacaan atau kelancaran. Dua anak dapat memakai metode dan beban berbeda, tetapi keduanya tetap diarahkan menuju kualitas yang baik.\n\nStandar menjelaskan bagaimana bagian dikerjakan. Beban menjelaskan seberapa besar bagian yang sedang dibawa."],
                            ],
                        ],
                    ],
                ],
                [
                    'slug' => 'hidup-bersama-al-quran',
                    'title' => 'Hidup Bersama Al-Qur’an — Bukan Sekadar Hafal',
                    'audience' => 'all',
                    'summary' => 'Menghubungkan bacaan, hafalan, murāja‘ah, pemahaman, pengamalan, dan istiqamah dalam satu perjalanan yang berkelanjutan.',
                    'description' => 'Program dasar ekosistem Sullamul Ḥifẓ untuk keluarga dan guru. Fokusnya bukan mempercepat jumlah hafalan, tetapi menjaga hubungan yang hidup dengan Al-Qur’an.',
                    'is_featured' => true,
                    'sort_order' => 5,
                    'modules' => [
                        [
                            'title' => '1. Bukan Sekadar Hafal, Tapi KUAT',
                            'summary' => 'Memahami arah perjalanan Sullamul Ḥifẓ.',
                            'lessons' => [
                                ['Lima Kekuatan dalam Perjalanan Al-Qur’an','article',7,"Sullamul Ḥifẓ mengarahkan pembinaan agar bacaan kuat, hafalan kuat, pemahaman bertumbuh, pengamalan hidup, dan perjalanan dijaga dengan istiqamah.\n\nKelima kekuatan tidak digabung menjadi satu skor. Ia menjadi arah pembinaan agar keberhasilan tidak hanya diukur dari banyaknya ayat atau juz yang pernah disetorkan."],
                                ['KUAT sebagai Jalan Pembinaan','article',6,"K — Kenali Diri.\nU — Ukur Kemampuan, Usahakan Bertahap.\nA — Al-Qur’an Dipahami dan Diamalkan.\nT — Teguh Menjaga Perjalanan.\n\nKUAT adalah proses, bukan perlombaan. Setiap peserta memiliki tangga dan pijakan yang dapat berbeda."],
                            ],
                        ],
                        [
                            'title' => '2. Menjaga Hafalan Tetap Hidup',
                            'summary' => 'Murāja‘ah sebagai nafas hafalan.',
                            'lessons' => [
                                ['Murāja‘ah adalah Nafas Hafalan','activity',8,"Pilih satu surah lama. Dengarkan atau baca sekali, lalu coba panggil kembali tanpa melihat. Tandai bagian yang ragu dan ulang bagian tersebut.\n\nTujuannya bukan mencari kesalahan sebanyak-banyaknya, tetapi menjaga agar hafalan lama tetap dapat dipanggil dan tidak mati karena terus mengejar hafalan baru."],
                                ['Audio Qur’an sebagai Pendamping Latihan','audio',5,"Gunakan menu Audio di Academy atau Latihan Al-Qur’an untuk mendengar ayat dan melakukan pengulangan. Al-Husary dapat dipilih ketika membutuhkan artikulasi yang teliti; sumber lain dapat digunakan sesuai kebutuhan lembaga.\n\nAudio membantu mendengar dan menirukan. Talaqqi, koreksi guru, dan pembacaan langsung tetap menjadi fondasi."],
                                ['Dari Ayat ke Percakapan Kehidupan','activity',7,"Setelah membaca satu ayat atau surah, pilih satu pertanyaan sederhana: “Apa yang ayat ini ajarkan kepada kita hari ini?” atau “Apa satu tindakan kecil yang bisa dilakukan?”\n\nTidak semua ayat harus menghasilkan tugas. Tujuannya membiasakan Al-Qur’an hadir dalam percakapan keluarga dan kehidupan."],
                            ],
                        ],
                    ],
                ],
                [
                    'slug' => 'pendidikan-anak-adab-keteladanan',
                    'title' => 'Pendidikan Anak — Adab, Keteladanan, dan Pertumbuhan',
                    'audience' => 'guardian',
                    'summary' => 'Bekal keluarga untuk membangun hubungan, kebiasaan, tanggung jawab, keberanian, dan karakter tanpa mengubah setiap proses menjadi nilai.',
                    'description' => 'Program parenting yang menghubungkan pendidikan Al-Qur’an dengan kehidupan sehari-hari: komunikasi positif, pembiasaan, keteladanan, kegiatan pengembangan diri, dan percakapan keluarga.',
                    'is_featured' => false,
                    'sort_order' => 6,
                    'modules' => [
                        [
                            'title' => '1. Hubungan Sebelum Instruksi',
                            'summary' => 'Anak lebih mudah dibimbing ketika merasa aman dan dipahami.',
                            'lessons' => [
                                ['Mendengar Sebelum Mengoreksi','article',6,"Sebelum memberi instruksi, cari tahu kondisi anak. Terkadang kesulitan belajar bukan karena tidak mau, tetapi karena lelah, bingung dengan langkah, takut salah, atau membutuhkan jeda.\n\nMendengar bukan berarti semua keinginan dituruti. Mendengar membantu orang tua memilih cara membimbing yang lebih tepat."],
                                ['Apresiasi Usaha yang Spesifik','activity',5,"Alih-alih hanya berkata “pintar”, sebutkan usaha yang terlihat: “Tadi kamu mau mengulang bagian yang sulit tiga kali,” atau “Kamu tetap mencoba walau sempat lupa.”\n\nApresiasi spesifik membantu anak mengenali proses yang bisa diulang, bukan mengejar label atau pujian."],
                            ],
                        ],
                        [
                            'title' => '2. Karakter melalui Pengalaman',
                            'summary' => 'Program bakat dan kegiatan bersama sebagai ruang latihan kehidupan.',
                            'lessons' => [
                                ['Public Speaking sebagai Latihan Keberanian','article',6,"Public speaking bukan hanya soal tampil bagus. Ia dapat menjadi ruang latihan menyusun pikiran, berbicara dengan adab, mendengar orang lain, dan bertumbuh dari rasa gugup.\n\nFokuskan evaluasi pada keberanian mencoba dan satu keterampilan yang sedang dilatih, bukan membandingkan penampilan anak."],
                                ['Kreativitas, Gerak, dan Kerja Sama','article',6,"Menggambar, futsal, permainan kelompok, dan kegiatan kreatif dapat membantu anak berlatih disiplin, komunikasi, gerak, imajinasi, serta kerja sama. Program tambahan sebaiknya tetap terhubung dengan tujuan pembinaan manusia, bukan hanya mengisi jadwal."],
                                ['Percakapan Jumat di Rumah','checklist',5,"Setelah Pembinaan Jumat, pilih satu pertanyaan saja:\n• Apa yang paling kamu ingat?\n• Siapa tokoh atau kisah yang menarik?\n• Apa satu hal yang ingin dicoba pekan ini?\n\nTidak wajib membuat laporan setiap Jumat. Percakapan yang hangat sudah menjadi bagian dari tindak lanjut."],
                            ],
                        ],
                    ],
                ],
            ];

            foreach ($programs as $definition) {
                $program = AcademyProgram::firstOrCreate(
                    ['institution_id' => $institution->id, 'slug' => $definition['slug']],
                    [
                        'title' => $definition['title'],
                        'audience' => $definition['audience'],
                        'summary' => $definition['summary'],
                        'description' => $definition['description'],
                        'status' => 'published',
                        'is_featured' => $definition['is_featured'],
                        'sort_order' => $definition['sort_order'],
                    ]
                );

                $this->syncModules($program, $definition['modules']);
            }
        }
    }

    private function syncModules(AcademyProgram $program, array $modules): void
    {
        foreach ($modules as $moduleIndex => $moduleData) {
            $module = AcademyModule::firstOrCreate(
                ['academy_program_id' => $program->id, 'title' => $moduleData['title']],
                [
                    'summary' => $moduleData['summary'],
                    'sort_order' => $moduleIndex + 1,
                    'status' => 'published',
                ]
            );

            foreach ($moduleData['lessons'] as $lessonIndex => $lessonData) {
                [$title, $type, $minutes, $body] = array_slice($lessonData, 0, 4);
                $mediaUrl = $lessonData[4] ?? null;
                AcademyLesson::firstOrCreate(
                    ['academy_module_id' => $module->id, 'slug' => Str::slug($title)],
                    [
                        'title' => $title,
                        'lesson_type' => $type,
                        'summary' => Str::limit(preg_replace('/\s+/', ' ', $body), 175),
                        'body' => $body,
                        'media_url' => $mediaUrl,
                        'duration_minutes' => $minutes,
                        'sort_order' => $lessonIndex + 1,
                        'requires_action' => in_array($type, ['activity', 'checklist'], true),
                        'status' => 'published',
                    ]
                );
            }
        }
    }
}
