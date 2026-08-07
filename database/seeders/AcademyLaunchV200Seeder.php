<?php

namespace Database\Seeders;

use App\Models\AcademyLesson;
use App\Models\AcademyModule;
use App\Models\AcademyProgram;
use App\Models\Institution;
use App\Models\LaunchCheck;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AcademyLaunchV200Seeder extends Seeder
{
    public function run(): void
    {
        foreach (Institution::query()->where('status', 'active')->get() as $institution) {
            $parent = AcademyProgram::firstOrCreate(
                ['institution_id' => $institution->id, 'slug' => 'parent-academy-rumah-qurani'],
                [
                    'title' => 'Parent Academy — Rumah yang Dekat dengan Al-Qur’an',
                    'audience' => 'guardian',
                    'summary' => 'Bekal praktis bagi orang tua untuk mendampingi tahfizh, murajaah, komunikasi, adab, dan ritme belajar anak tanpa tekanan.',
                    'description' => 'Orang tua tidak harus menjadi guru tahfizh. Peran utamanya adalah membangun suasana, perhatian, keteladanan, komunikasi, dan dukungan yang membantu perjalanan anak bersama Al-Qur’an.',
                    'status' => 'published',
                    'is_featured' => true,
                    'sort_order' => 1,
                ]
            );

            $parentModules = [
                [
                    'title' => '1. Peran Orang Tua',
                    'summary' => 'Memahami posisi orang tua sebagai partner pembinaan, bukan penguji hafalan.',
                    'lessons' => [
                        ['Orang Tua sebagai Pendamping, Bukan Penguji','article',6,'Orang tua tidak perlu mengambil alih peran guru. Di rumah, fokus utama adalah membangun rasa aman, kebiasaan baik, perhatian, dan dukungan.\n\nGunakan pertanyaan yang hangat: “Bagian mana yang ingin diulang bersama?” alih-alih “Kok belum hafal?”. Apresiasi usaha yang nyata, bukan membandingkan anak dengan santri lain.\n\nBila ada kesulitan bacaan atau target yang terasa berat, catat dan sampaikan melalui Buku Penghubung agar guru dapat menentukan tindak lanjut.'],
                        ['Membangun Suasana Rumah yang Dekat dengan Al-Qur’an','checklist',8,'Mulai dari hal yang sederhana dan dapat dijaga.\n\nChecklist keluarga:\n• Sediakan waktu singkat tanpa gangguan.\n• Dengarkan murattal bersama bila membantu.\n• Orang tua ikut membaca atau menyimak dengan tenang.\n• Hindari membandingkan kecepatan hafalan.\n• Akhiri latihan dengan apresiasi dan doa.\n\nTidak semua hari harus menghasilkan hafalan baru. Menjaga ritme dan hubungan yang sehat juga bagian dari perjalanan.'],
                    ],
                ],
                [
                    'title' => '2. Mendampingi Hafalan & Murajaah',
                    'summary' => 'Cara menggunakan target guru dan latihan murattal sebagai pendamping, bukan pengganti talaqqi.',
                    'lessons' => [
                        ['Mendampingi Tahfizh tanpa Tekanan','article',7,'Target membantu memberi arah, tetapi nilai anak tidak ditentukan oleh banyaknya hafalan.\n\nDampingi target yang diberikan guru sesuai kemampuan hari itu. Bila anak lelah atau kehilangan ritme, kurangi beban dan jaga bagian yang sudah dimiliki. Guru tetap menjadi rujukan untuk koreksi bacaan dan penyesuaian target.'],
                        ['Cara Membantu Murajaah di Rumah','activity',8,'Pilih satu bagian yang sudah pernah disetorkan. Dengarkan anak membaca tanpa terlalu cepat memotong. Tandai bagian yang terasa ragu, lalu ulangi bagian tersebut.\n\nAktivitas 10 menit:\n1. Baca atau dengarkan satu kali.\n2. Ulang bagian yang ragu.\n3. Panggil kembali tanpa melihat bila siap.\n4. Catat satu bagian yang perlu dibawa ke guru.'],
                        ['Menggunakan Murattal 10× dengan Bijak','activity',6,'Fitur Latihan Al-Qur’an dapat mengulang satu ayat atau rentang ayat. Untuk hafalan baru, Al-Husary menjadi pilihan utama karena tempo dan artikulasinya membantu latihan yang teliti. Al-Minshawi dapat menjadi pilihan untuk murajaah yang lebih tenang.\n\nMurattal membantu mendengar dan menirukan. Ia tidak menggantikan talaqqi dan koreksi guru. Gunakan pengulangan secukupnya; berhenti bila anak sudah lelah atau mulai kehilangan fokus.'],
                    ],
                ],
                [
                    'title' => '3. Memahami Perkembangan Anak',
                    'summary' => 'Membaca perkembangan sebagai perjalanan, bukan perlombaan.',
                    'lessons' => [
                        ['Membaca Perkembangan Anak dengan Hangat','article',6,'Perhatikan perubahan kecil yang bermakna: bacaan lebih tenang, kesalahan berkurang, anak lebih mudah kembali setelah lupa, atau mulai mau berlatih tanpa banyak dorongan.\n\nLaporan Sullamul Ḥifẓ tidak dibuat untuk membuat ranking. Gunakan catatan guru untuk memahami kebutuhan berikutnya dan pilih satu tindak lanjut yang realistis di rumah.'],
                        ['Ketika Anak Kehilangan Ritme','article',7,'Jeda, lelah, sakit, perubahan jadwal, atau kejenuhan dapat memengaruhi ritme. Jangan langsung menyimpulkan anak malas atau tidak mampu.\n\nMulai kembali dari pijakan yang nyata: bagian pendek yang sudah dikenal, waktu latihan yang lebih singkat, dan komunikasi dengan guru. Tujuan pertama adalah kembali ke arah yang benar, bukan mengejar ketertinggalan sekaligus.'],
                    ],
                ],
                [
                    'title' => '4. Komunikasi Guru–Orang Tua',
                    'summary' => 'Menjaga informasi penting agar pembinaan di kelas dan rumah tidak terputus.',
                    'lessons' => [
                        ['Komunikasi Positif Guru dan Orang Tua','article',6,'Sampaikan informasi yang membantu keputusan atau tindak lanjut: bagian yang perlu diulang, respons anak terhadap metode tertentu, kendala di rumah, atau perubahan kondisi yang memengaruhi belajar.\n\nGunakan bahasa yang spesifik dan tidak memberi label negatif pada anak. Fokus pada kondisi, kebutuhan, dan langkah berikutnya.'],
                        ['Cara Menggunakan Buku Penghubung','checklist',5,'Buku Penghubung adalah ruang privat antara guru dan orang tua.\n\nGunakan untuk:\n• menanyakan target atau bagian yang perlu diulang;\n• menyampaikan kendala penting;\n• mengirim tanggapan singkat terhadap tugas;\n• menyepakati tindak lanjut.\n\nCatatan pribadi anak tidak dipindahkan ke ruang community atau pengumuman umum.'],
                    ],
                ],
                [
                    'title' => '5. Teladan, Adab, dan Istiqamah',
                    'summary' => 'Menempatkan hafalan di dalam kehidupan keluarga yang lebih luas.',
                    'lessons' => [
                        ['Orang Tua sebagai Teladan','article',6,'Anak belajar bukan hanya dari instruksi. Kebiasaan orang tua membaca, mendengarkan dengan hormat, menjaga waktu, meminta maaf, dan memperbaiki diri adalah bagian dari pendidikan.\n\nPilih satu kebiasaan keluarga yang dapat dijaga. Kecil tetapi konsisten lebih baik daripada program besar yang segera ditinggalkan.'],
                        ['Adab dan Karakter dalam Perjalanan Al-Qur’an','activity',7,'Hubungkan satu ayat atau surat yang sedang dipelajari dengan perilaku sehari-hari. Tanyakan: “Apa satu hal yang bisa kita lakukan hari ini?”\n\nTidak perlu memberi angka pada kesalehan atau akhlak. Gunakan percakapan, keteladanan, dan tindak lanjut sederhana.'],
                    ],
                ],
            ];

            $this->syncModules($parent, $parentModules);

            $teacher = AcademyProgram::firstOrCreate(
                ['institution_id' => $institution->id, 'slug' => 'orientasi-guru-sullamul-hifz'],
                [
                    'title' => 'Orientasi Guru Sullamul Ḥifẓ',
                    'audience' => 'teacher',
                    'summary' => 'Panduan ringkas agar guru menggunakan aplikasi sebagai pencatat dan penghubung tanpa membebani proses pembinaan.',
                    'description' => 'Materi internal untuk menjaga konsistensi filosofi, alur pertemuan, komunikasi dengan keluarga, serta prinsip Human Before Data dan No Ranking Culture.',
                    'status' => 'published',
                    'is_featured' => false,
                    'sort_order' => 2,
                ]
            );

            $teacherModules = [
                [
                    'title' => '1. Prinsip Kerja Guru',
                    'summary' => 'Catat yang penting, bukan catat semuanya.',
                    'lessons' => [
                        ['Minimum Meaningful Documentation','article',5,'Catat informasi yang membantu keputusan guru, membantu orang tua, menjaga kesinambungan, menjadi dasar tindak lanjut, atau mendokumentasikan perjalanan penting. Kolom opsional tidak perlu dipaksa bila tidak ada informasi yang bermakna.'],
                        ['Human Before Data & No Ranking Culture','article',5,'Santri dipandang sebagai manusia yang bertumbuh, bukan objek target. Hindari ranking berdasarkan jumlah hafalan, kecepatan, profil STIFIn, atau gabungan nilai perkembangan. Laporan harus membantu pembinaan, bukan menciptakan perbandingan yang merusak.'],
                    ],
                ],
                [
                    'title' => '2. Alur Pertemuan',
                    'summary' => 'Aplikasi mengikuti kegiatan nyata guru.',
                    'lessons' => [
                        ['Pertemuan → Kehadiran → Pembelajaran → Tindak Lanjut','checklist',6,'Gunakan satu alur sederhana: buka pertemuan, tandai kehadiran, catat pembelajaran yang penting, tambahkan catatan individual hanya bila perlu, berikan tugas atau tindak lanjut jika diperlukan, lalu tutup pertemuan.'],
                        ['Membuat Target yang Realistis','article',6,'Marhalah menunjukkan besarnya beban hafalan baru, bukan kelas, kecerdasan, atau martabat santri. Sesuaikan beban dengan kesiapan nyata dan kualitas bagian yang dibawa. Rubu’ berfungsi sebagai milestone perjalanan, bukan ranking.'],
                    ],
                ],
                [
                    'title' => '3. Kemitraan dengan Keluarga',
                    'summary' => 'Memberi arahan yang dapat dilakukan di rumah.',
                    'lessons' => [
                        ['Catatan Guru yang Membantu Orang Tua','activity',6,'Tulis catatan singkat dengan tiga unsur: apa yang terjadi, bagian yang perlu dibantu, dan langkah berikutnya. Contoh: “Hari ini Al-Qari’ah 1–5 sudah lebih lancar. Mohon ulang ayat 3–4 dengan Al-Husary 10× per ayat, lalu beri kabar bila masih terasa berat.”'],
                        ['Merekomendasikan Materi Parent Academy','activity',5,'Gunakan rekomendasi Academy hanya ketika relevan dengan kebutuhan anak atau keluarga. Jangan mengirim terlalu banyak materi sekaligus. Satu rekomendasi yang tepat dan dapat dijalankan lebih berguna daripada daftar panjang yang tidak selesai.'],
                    ],
                ],
            ];

            $this->syncModules($teacher, $teacherModules);

            if (class_exists(LaunchCheck::class)) {
                foreach ([
                    ['academy_parent','Academy','Parent Academy dapat dibuka dan progress materi dapat disimpan'],
                    ['academy_teacher','Academy','Guru dapat merekomendasikan materi Academy kepada wali sesuai santri'],
                    ['mobile_senior','Perangkat','PWA diuji pada ponsel dengan tombol besar dan tanpa scroll horizontal'],
                    ['quran_simple','Pembelajaran','Quran Player mode sederhana diuji oleh wali tanpa bantuan teknis'],
                ] as [$key,$category,$label]) {
                    LaunchCheck::firstOrCreate(
                        ['institution_id'=>$institution->id,'check_key'=>$key],
                        ['category'=>$category,'label'=>$label,'status'=>'pending']
                    );
                }
            }
        }
    }

    private function syncModules(AcademyProgram $program, array $modules): void
    {
        foreach ($modules as $moduleIndex => $moduleData) {
            $module = AcademyModule::firstOrCreate(
                ['academy_program_id' => $program->id, 'title' => $moduleData['title']],
                ['summary' => $moduleData['summary'], 'sort_order' => $moduleIndex + 1, 'status' => 'published']
            );

            foreach ($moduleData['lessons'] as $lessonIndex => [$title,$type,$minutes,$body]) {
                AcademyLesson::firstOrCreate(
                    ['academy_module_id' => $module->id, 'slug' => Str::slug($title)],
                    [
                        'title' => $title,
                        'lesson_type' => $type,
                        'summary' => Str::limit(preg_replace('/\s+/', ' ', $body), 170),
                        'body' => $body,
                        'duration_minutes' => $minutes,
                        'sort_order' => $lessonIndex + 1,
                        'requires_action' => in_array($type, ['activity','checklist'], true),
                        'status' => 'published',
                    ]
                );
            }
        }
    }
}
