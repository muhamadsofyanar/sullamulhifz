<?php

namespace Database\Seeders;

use App\Models\FeatureFlag;
use App\Models\Institution;
use App\Models\LaunchCheck;
use App\Models\MarhalahType;
use App\Models\QuranHeritageTerm;
use App\Models\QuranProgramStep;
use App\Models\QuranProgramTemplate;
use App\Models\QuranRubu;
use App\Services\QuranDivisionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class QuranJourneyV260Seeder extends Seeder
{
    public function run(): void
    {
        $marhalah = [
            ['ayah','Āyah',1,null,30,30,'ayah',1,'1 ayat atau lebih','Tahap Juz 30. Satu porsi hafalan baru/setoran minimal satu ayat; dapat lebih sesuai panjang ayat dan kesiapan santri.'],
            ['tsalatsiyyah','Tsalātsiyyah',2,3,29,29,'line',3,'3 baris','Tahap Juz 29. Porsi minimal dikunci tiga baris Mushaf Madinah untuk menjaga kapasitas hafalan baru dan setoran.'],
            ['khamsiyyah','Khamsiyyah',3,5,28,28,'line',5,'5 baris','Tahap Juz 28. Porsi minimal lima baris Mushaf Madinah.'],
            ['nisfiyyah','Niṣfiyyah',4,null,27,27,'page',0.5,'½ halaman','Tahap Juz 27. Porsi minimal setengah halaman Mushaf Madinah.'],
            ['safhah','Ṣafḥah',5,null,26,26,'page',1,'1 halaman','Tahap Juz 26. Porsi minimal satu halaman Mushaf Madinah.'],
            ['safhatayn','Ṣafḥatayn',6,null,1,25,'page',2,'2 halaman','Tahap Juz 1–25. Porsi minimal dua halaman Mushaf Madinah per sesi hafalan/setoran.'],
        ];

        foreach ($marhalah as [$code,$name,$sequence,$lineCount,$juzFrom,$juzTo,$unit,$value,$label,$description]) {
            MarhalahType::query()->updateOrCreate(
                ['code'=>$code],
                [
                    'name'=>$name,
                    'sequence'=>$sequence,
                    'line_count'=>$lineCount,
                    'juz_from'=>$juzFrom,
                    'juz_to'=>$juzTo,
                    'portion_unit'=>$unit,
                    'portion_value'=>$value,
                    'portion_label'=>$label,
                    'description'=>$description,
                    'journey_note'=>'Porsi adalah standar satu sesi hafalan/setoran, bukan kewajiban harus dilakukan setiap hari. Frekuensi dapat harian, mingguan, atau fleksibel sesuai program guru.',
                    'status'=>'active',
                ],
            );
        }

        // Data quran_rubus v1.5 adalah delapan segment internal Juz 30, bukan Rubu‘ al-Ḥizb standar.
        // Nama lama dipertahankan di database untuk kompatibilitas relasi, tetapi label pengguna diluruskan.
        if (Schema::hasTable('quran_rubus')) {
            QuranRubu::query()->where('juz_number',30)->orderBy('rubu_number')->get()->each(function (QuranRubu $segment): void {
                $suffix = preg_replace("/^(?:Rubu['’]?\\s*\\d+|Segment Juz 30\\s+\\d+)\\s*[—-]\\s*/u", '', (string)$segment->name);
                $segment->update([
                    'name'=>'Segment Juz 30 '.$segment->rubu_number.' — '.($suffix ?: 'bagian hafalan'),
                    'description'=>'Segment penjagaan internal Sullamul Ḥifẓ versi lama. Jangan disamakan dengan Rubu‘ al-Ḥizb standar 1–240.',
                ]);
            });
        }

        $khatam30 = QuranProgramTemplate::query()->updateOrCreate(
            ['code'=>'khatam-30-hari'],
            [
                'name'=>'Khatam Al-Qur’an 30 Hari',
                'program_type'=>'khatam_30',
                'duration_days'=>30,
                'description'=>'Satu juz per langkah. Dapat dipakai untuk tilawah, murāja‘ah hafalan, atau keduanya. Hari yang terlewat tidak diberi label gagal; perjalanan dapat dilanjutkan.',
                'scholarly_note'=>'Pembagian 30 juz digunakan sebagai alat bantu pembagian bacaan. Program ini memakai satu juz sebagai satu porsi program.',
                'status'=>'active',
            ],
        );
        for ($juz=1; $juz<=30; $juz++) {
            QuranProgramStep::query()->updateOrCreate(
                ['quran_program_template_id'=>$khatam30->id,'sequence'=>$juz],
                [
                    'mnemonic_letter'=>null,
                    'label'=>'Juz '.$juz,
                    'start_surah_id'=>null,
                    'end_surah_id'=>null,
                    'start_juz'=>$juz,
                    'end_juz'=>$juz,
                    'description'=>'Porsi ke-'.$juz.' dari program khatam 30 bagian.',
                ],
            );
        }

        $fami = QuranProgramTemplate::query()->updateOrCreate(
            ['code'=>'fami-bisyauqin'],
            [
                'name'=>'Fami Bisyauqin — 7 Manzil',
                'program_type'=>'fami_bisyauqin',
                'duration_days'=>7,
                'description'=>'Tujuh manzil untuk tilawah atau murāja‘ah: Fa · Mim · Ya · Ba · Syin · Wau · Qaf. Porsinya berbasis kelompok surah, bukan pembagian halaman yang sama rata.',
                'scholarly_note'=>'Fami Bisyauqin dipakai sebagai mnemonik tujuh bagian bacaan. Sullamul Ḥifẓ menampilkannya sebagai khazanah pembagian bacaan dan penjagaan Al-Qur’an.',
                'status'=>'active',
            ],
        );
        $famiSteps = [
            [1,'ف · Fa','Fa — Al-Fātiḥah sampai An-Nisā’',1,4],
            [2,'م · Mim','Mim — Al-Mā’idah sampai At-Taubah',5,9],
            [3,'ي · Ya','Ya — Yūnus sampai An-Naḥl',10,16],
            [4,'ب · Ba','Ba — Al-Isrā’ sampai Al-Furqān',17,25],
            [5,'ش · Syin','Syin — Asy-Syu‘arā’ sampai Yāsīn',26,36],
            [6,'و · Wau','Wau — Aṣ-Ṣāffāt sampai Al-Ḥujurāt',37,49],
            [7,'ق · Qaf','Qaf — Qāf sampai An-Nās',50,114],
        ];
        foreach ($famiSteps as [$sequence,$letter,$label,$startSurah,$endSurah]) {
            QuranProgramStep::query()->updateOrCreate(
                ['quran_program_template_id'=>$fami->id,'sequence'=>$sequence],
                [
                    'mnemonic_letter'=>$letter,
                    'label'=>$label,
                    'start_surah_id'=>$startSurah,
                    'end_surah_id'=>$endSurah,
                    'start_juz'=>null,
                    'end_juz'=>null,
                    'description'=>'Manzil ke-'.$sequence.' dalam pola Fami Bisyauqin.',
                ],
            );
        }

        $terms = [
            ['ayah','Āyah','آية','Unit teks Al-Qur’an yang menjadi dasar paling kecil dalam pencatatan hafalan.','Dipakai untuk target, setoran, murāja‘ah, audio, dan penunjuk mushaf.',1],
            ['page','Halaman Mushaf',null,'Nomor halaman pada Mushaf Madinah 604 halaman yang menjadi acuan porsi halaman Sullamul Ḥifẓ.','Menjadi acuan Niṣfiyyah, Ṣafḥah, dan Ṣafḥatayn.',2],
            ['juz','Juz','جزء','Pembagian Al-Qur’an menjadi 30 bagian untuk membantu pengaturan bacaan.','Menjadi jalur Marhalah, milestone hafalan, dan program khatam 30 hari.',3],
            ['hizb','Ḥizb','حزب','Satu juz terbagi menjadi dua ḥizb; seluruh Al-Qur’an memiliki 60 ḥizb.','Dapat dipakai sebagai porsi tilawah atau penjagaan yang lebih kecil daripada satu juz.',4],
            ['rubu','Rubu‘ al-Ḥizb','ربع الحزب','Seperempat ḥizb; keseluruhan korpus memiliki 240 rubu‘ al-ḥizb.','Dipakai sebagai porsi kecil murāja‘ah dan milestone penjagaan.',5],
            ['manzil','Manzil','منزل','Pembagian bacaan menjadi tujuh bagian dalam pola yang dikenal melalui mnemonik Fami Bisyauqin.','Dipakai untuk program khatam atau murāja‘ah tujuh bagian.',6],
            ['ruku','Rukū‘','ركوع','Penanda kelompok ayat yang dikenal dalam tradisi penataan mushaf dan pembacaan.','Dikenalkan sebagai literasi mushaf; pengguna dapat melihat batasnya dari metadata korpus.',7],
            ['waqaf','Waqaf','وقف','Tanda dan ilmu berhenti/menyambung bacaan agar makna dan ketepatan bacaan terjaga.','Dihubungkan dengan pembelajaran tahsīn dan adab membaca.',8],
            ['sajdah','Ayat Sajdah','سجدة','Ayat yang diberi penanda sajdah dalam mushaf.','Ditampilkan sebagai informasi mushaf agar pengguna mengenali penandanya.',9],
            ['makki-madani','Makkiyah & Madaniyah',null,'Klasifikasi surah berdasarkan periode turunnya wahyu dalam kajian Ulumul Qur’an.','Ditampilkan sebagai literasi dasar surah, bukan penilaian tingkat kesulitan.',10],
        ];
        foreach ($terms as [$code,$name,$arabic,$description,$use,$order]) {
            QuranHeritageTerm::query()->updateOrCreate(
                ['code'=>$code],
                [
                    'name'=>$name,
                    'arabic_name'=>$arabic,
                    'short_description'=>$description,
                    'practical_use'=>$use,
                    'context_note'=>'Penanda/pembagian ini membantu interaksi dengan mushaf dan pembelajaran; ia bukan tambahan pada teks wahyu Al-Qur’an.',
                    'sort_order'=>$order,
                    'status'=>'active',
                ],
            );
        }

        if (Schema::hasTable('quran_division_units') && Schema::hasTable('quran_ayahs')) {
            app(QuranDivisionService::class)->sync();
        }

        $checks = [
            ['phase4_marhalah_flow','Roadmap Fase 4','Marhalah mengikuti Juz: 30 Āyah, 29 Tsalātsiyyah, 28 Khamsiyyah, 27 Niṣfiyyah, 26 Ṣafḥah, lalu Juz 1–25 Ṣafḥatayn; tidak ada naik/turun level bebas.'],
            ['phase4_milestone_retention','Roadmap Fase 4','Milestone membedakan selesai hafalan, penguatan, dan terjaga; pemeriksaan penjagaan tersimpan sebagai histori.'],
            ['phase4_khatam_30','Roadmap Fase 4','Program Khatam 30 Hari diuji end-to-end dengan satu juz per langkah dan tanpa label gagal saat terlambat.'],
            ['phase4_fami_bisyauqin','Roadmap Fase 4','Program Fami Bisyauqin tujuh manzil diuji dari Fa sampai Qaf untuk tilawah/murāja‘ah.'],
            ['phase4_heritage_terms','Roadmap Fase 4','Peta Mushaf menampilkan Juz, Ḥizb, Rubu‘, Manzil, Rukū‘, Waqaf, Sajdah, serta Makki/Madani dengan konteks edukatif.'],
            ['phase4_guardian_visibility','Roadmap Fase 4','Wali dapat melihat Marhalah, milestone, penjagaan, dan program Qur’an anak tanpa mengubah keputusan guru.'],
            ['phase4_academy_journey','Roadmap Fase 4','Program Qur’an dapat dijalankan langsung di domain Academy tanpa memindahkan pengguna ke aplikasi operasional.'],
            ['phase4_mobile_workflow','Roadmap Fase 4','Qur’an Journey nyaman digunakan guru dan wali dari ponsel.'],
        ];

        foreach (Institution::query()->where('status','active')->get() as $institution) {
            FeatureFlag::query()->firstOrCreate(
                ['institution_id'=>$institution->id,'feature_key'=>'quran_journey'],
                ['enabled'=>true],
            );
            foreach ($checks as [$key,$category,$label]) {
                LaunchCheck::query()->updateOrCreate(
                    ['institution_id'=>$institution->id,'check_key'=>$key],
                    ['category'=>$category,'label'=>$label],
                );
            }
        }
    }
}
