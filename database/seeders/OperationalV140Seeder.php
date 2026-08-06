<?php

namespace Database\Seeders;

use App\Models\PublicPage;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class OperationalV140Seeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            ['slug'=>'syarat-ketentuan','title'=>'Syarat dan Ketentuan','summary'=>'Pedoman penggunaan website dan aplikasi Sullamul Ḥifẓ.','content'=>'Pengguna wajib menjaga kerahasiaan akun, memakai data untuk kepentingan pendidikan, dan melaporkan dugaan penyalahgunaan kepada pengelola.','status'=>'published','sort_order'=>90],
        ];

        foreach ($pages as $page) {
            PublicPage::updateOrCreate(['slug'=>$page['slug']], $page);
        }

        foreach ([
            'release_version'=>'1.4.0',
            'release_name'=>'TPA Operational Complete',
            'database_upgrade'=>'additive',
        ] as $key=>$value) {
            SystemSetting::updateOrCreate(['institution_id'=>null,'key'=>$key],['group'=>'release','value'=>$value,'type'=>'string']);
        }
    }
}
