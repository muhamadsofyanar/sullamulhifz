<?php

namespace Database\Seeders;

use App\Models\Institution;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class LaunchTemplateV190Seeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            'teacher_comment_positive' => 'Alhamdulillah, perkembangan hari ini baik. Lanjutkan latihan secara singkat dan konsisten di rumah.',
            'teacher_comment_repeat' => 'Bagian ini masih perlu diulang dengan tenang. Dengarkan murattal, tirukan, lalu ulangi tanpa terburu-buru.',
            'guardian_follow_up' => 'Mohon dampingi latihan sesuai target. Apresiasi usaha anak dan sampaikan kendala melalui Buku Penghubung.',
            'friday_family_activity' => 'Pilih satu surat yang sudah dikenal, baca bersama, lalu bicarakan satu pesan yang dapat diamalkan keluarga.',
            'report_principle' => 'Rapor menampilkan perjalanan perkembangan, bukan perbandingan dengan santri lain.',
            'media_policy' => 'Gunakan checklist atau catatan teks bila unggahan media tidak diperlukan. Media anak hanya untuk pihak berwenang.',
            'launch_status' => 'pilot',
        ];

        foreach (Institution::where('status', 'active')->get() as $institution) {
            foreach ($templates as $key => $value) {
                SystemSetting::firstOrCreate(
                    ['institution_id' => $institution->id, 'key' => 'v190.'.$key],
                    ['group' => 'launch_templates', 'value' => $value, 'type' => 'string']
                );
            }
        }
    }
}
