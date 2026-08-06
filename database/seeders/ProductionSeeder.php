<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Institution;
use App\Models\LearningGroup;
use App\Models\Level;
use App\Models\MarhalahType;
use App\Models\Permission;
use App\Models\Program;
use App\Models\QuranSurah;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        $institution = Institution::updateOrCreate(
            ['code' => env('INITIAL_INSTITUTION_CODE', 'ALINSYIRAH')],
            [
                'name' => env('INITIAL_INSTITUTION_NAME', 'TPA Al-Insyirah'),
                'slug' => Str::slug(env('INITIAL_INSTITUTION_NAME', 'TPA Al-Insyirah')),
                'timezone' => 'Asia/Jakarta',
                'status' => 'active',
            ]
        );

        $roles = [
            'superadmin' => 'Superadmin',
            'institution_admin' => 'Admin Lembaga',
            'head' => 'Kepala TPA',
            'teacher' => 'Guru',
            'guardian' => 'Orang Tua/Wali',
        ];

        foreach ($roles as $name => $displayName) {
            Role::updateOrCreate(['name' => $name], ['display_name' => $displayName, 'scope' => $name === 'superadmin' ? 'system' : 'institution']);
        }

        $permissions = [
            'dashboard.view' => 'Melihat dashboard',
            'academic.manage' => 'Mengelola data akademik',
            'students.manage' => 'Mengelola santri',
            'teachers.manage' => 'Mengelola guru',
            'meetings.manage' => 'Mengelola pertemuan',
            'attendance.manage' => 'Mengelola absensi',
            'learning.manage' => 'Mengelola catatan pembelajaran',
            'assignments.manage' => 'Mengelola tugas',
            'assignments.submit' => 'Mengirim bukti tugas',
            'liaison.manage' => 'Mengelola buku penghubung',
            'announcements.manage' => 'Mengelola pengumuman',
            'friday.manage' => 'Mengelola Pembinaan Jumat',
            'reports.view' => 'Melihat laporan',
        ];

        foreach ($permissions as $name => $displayName) {
            Permission::updateOrCreate(['name' => $name], ['display_name' => $displayName]);
        }

        $roleMap = [
            'superadmin' => array_keys($permissions),
            'institution_admin' => array_keys($permissions),
            'head' => ['dashboard.view','meetings.manage','attendance.manage','learning.manage','liaison.manage','announcements.manage','friday.manage','reports.view'],
            'teacher' => ['dashboard.view','meetings.manage','attendance.manage','learning.manage','assignments.manage','liaison.manage','friday.manage'],
            'guardian' => ['dashboard.view','assignments.submit','liaison.manage'],
        ];

        foreach ($roleMap as $roleName => $permissionNames) {
            $role = Role::where('name', $roleName)->firstOrFail();
            $ids = Permission::whereIn('name', $permissionNames)->pluck('id');
            $role->permissions()->sync($ids);
        }

        $adminEmail = env('INITIAL_ADMIN_EMAIL', 'admin@sullamulhifz.id');
        $admin = User::where('email', $adminEmail)->first();
        if (! $admin) {
            $admin = User::create([
                'institution_id' => $institution->id,
                'name' => env('INITIAL_ADMIN_NAME', 'Administrator TPA Al-Insyirah'),
                'email' => $adminEmail,
                'phone' => env('INITIAL_ADMIN_PHONE'),
                'password' => Hash::make(env('INITIAL_ADMIN_PASSWORD', 'Ganti-Segera-2026!')),
                'status' => 'active',
                'must_change_password' => true,
                'email_verified_at' => now(),
            ]);
        } else {
            $admin->update([
                'institution_id' => $institution->id,
                'name' => env('INITIAL_ADMIN_NAME', $admin->name),
                'phone' => env('INITIAL_ADMIN_PHONE', $admin->phone),
                'status' => 'active',
            ]);
        }
        $adminRole = Role::where('name', 'institution_admin')->firstOrFail();
        $admin->roles()->syncWithoutDetaching([$adminRole->id => ['institution_id' => $institution->id, 'status' => 'active']]);

        $year = AcademicYear::updateOrCreate(
            ['institution_id' => $institution->id, 'name' => '2026/2027'],
            ['start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'status' => 'active', 'is_active' => true]
        );

        $levels = [
            ['name' => 'Tamhidi', 'code' => 'TAMHIDI', 'sequence' => 1],
            ['name' => 'Mustawa Awal', 'code' => 'MUSTAWA-AWAL', 'sequence' => 2],
            ['name' => 'Mustawa Tsani', 'code' => 'MUSTAWA-TSANI', 'sequence' => 3],
        ];
        foreach ($levels as $data) {
            Level::updateOrCreate(['institution_id' => $institution->id, 'code' => $data['code']], $data + ['institution_id' => $institution->id, 'status' => 'active']);
        }

        $classDefinitions = [
            ['level' => 'TAMHIDI', 'name' => 'Tamhidi A', 'code' => 'TAMHIDI-A', 'capacity' => 18],
            ['level' => 'TAMHIDI', 'name' => 'Tamhidi B', 'code' => 'TAMHIDI-B', 'capacity' => 13],
            ['level' => 'MUSTAWA-AWAL', 'name' => 'Mustawa Awal A', 'code' => 'MUSTAWA-AWAL-A', 'capacity' => 16],
            ['level' => 'MUSTAWA-AWAL', 'name' => 'Mustawa Awal B', 'code' => 'MUSTAWA-AWAL-B', 'capacity' => 14],
            ['level' => 'MUSTAWA-TSANI', 'name' => 'Mustawa Tsani A', 'code' => 'MUSTAWA-TSANI-A', 'capacity' => 14],
            ['level' => 'MUSTAWA-TSANI', 'name' => 'Mustawa Tsani B', 'code' => 'MUSTAWA-TSANI-B', 'capacity' => 13],
        ];
        foreach ($classDefinitions as $class) {
            $level = Level::where('institution_id', $institution->id)->where('code', $class['level'])->firstOrFail();
            SchoolClass::updateOrCreate(
                ['academic_year_id' => $year->id, 'code' => $class['code']],
                ['institution_id' => $institution->id, 'level_id' => $level->id, 'name' => $class['name'], 'capacity' => $class['capacity'], 'status' => 'active']
            );
        }

        $programs = [
            ['name'=>'Pembelajaran TPA','code'=>'TPA','category'=>'quran'],
            ['name'=>'Tahsīn','code'=>'TAHSIN','category'=>'quran'],
            ['name'=>'Tahfizh','code'=>'TAHFIZH','category'=>'quran'],
            ['name'=>'Murāja‘ah','code'=>'MURAJAAH','category'=>'quran'],
            ['name'=>'Pembinaan Jumat','code'=>'PEMBINAAN-JUMAT','category'=>'character'],
            ['name'=>'Public Speaking','code'=>'PUBLIC-SPEAKING','category'=>'talent'],
            ['name'=>'Menggambar','code'=>'MENGGAMBAR','category'=>'talent'],
            ['name'=>'Futsal','code'=>'FUTSAL','category'=>'talent'],
        ];
        foreach ($programs as $program) {
            Program::updateOrCreate(['institution_id'=>$institution->id,'code'=>$program['code']], $program + ['institution_id'=>$institution->id,'status'=>'active']);
        }

        $tahfizh = Program::where('institution_id',$institution->id)->where('code','TAHFIZH')->firstOrFail();
        foreach ([['Tahfizh Sesi A','TAHFIZH-A',30],['Tahfizh Sesi B','TAHFIZH-B',27]] as [$name,$code,$capacity]) {
            LearningGroup::updateOrCreate(['academic_year_id'=>$year->id,'code'=>$code], ['institution_id'=>$institution->id,'program_id'=>$tahfizh->id,'name'=>$name,'capacity'=>$capacity,'status'=>'active']);
        }

        $marhalah = [
            ['ayah','Āyah',1,null,'Satu ayat pendek, satu ayat utuh, atau bagian ayat jika terlalu panjang.'],
            ['tsalatsiyyah','Tsalātsiyyah',2,3,'Beban tiga baris Mushaf Madinah.'],
            ['khamsiyyah','Khamsiyyah',3,5,'Beban lima baris Mushaf Madinah.'],
            ['nisfiyyah','Niṣfiyyah',4,8,'Beban sekitar tujuh sampai delapan baris Mushaf Madinah.'],
            ['safhah','Ṣafḥah',5,15,'Beban satu halaman atau lima belas baris Mushaf Madinah.'],
            ['safhatayn','Ṣafḥatayn',6,30,'Beban dua halaman atau tiga puluh baris Mushaf Madinah.'],
        ];
        foreach ($marhalah as [$code,$name,$sequence,$lineCount,$description]) {
            MarhalahType::updateOrCreate(['code'=>$code], compact('name','sequence','description') + ['line_count'=>$lineCount,'status'=>'active']);
        }

        $surahs = [
            [78,'النبأ','An-Naba’',40],[79,'النازعات','An-Nāzi‘āt',46],[80,'عبس','‘Abasa',42],[81,'التكوير','At-Takwīr',29],
            [82,'الإنفطار','Al-Infiṭār',19],[83,'المطففين','Al-Muṭaffifīn',36],[84,'الإنشقاق','Al-Insyiqāq',25],[85,'البروج','Al-Burūj',22],
            [86,'الطارق','Aṭ-Ṭāriq',17],[87,'الأعلى','Al-A‘lā',19],[88,'الغاشية','Al-Gāsyiyah',26],[89,'الفجر','Al-Fajr',30],
            [90,'البلد','Al-Balad',20],[91,'الشمس','Asy-Syams',15],[92,'الليل','Al-Lail',21],[93,'الضحى','Aḍ-Ḍuḥā',11],
            [94,'الشرح','Asy-Syarḥ',8],[95,'التين','At-Tīn',8],[96,'العلق','Al-‘Alaq',19],[97,'القدر','Al-Qadr',5],
            [98,'البينة','Al-Bayyinah',8],[99,'الزلزلة','Az-Zalzalah',8],[100,'العاديات','Al-‘Ādiyāt',11],[101,'القارعة','Al-Qāri‘ah',11],
            [102,'التكاثر','At-Takāṡur',8],[103,'العصر','Al-‘Aṣr',3],[104,'الهمزة','Al-Humazah',9],[105,'الفيل','Al-Fīl',5],
            [106,'قريش','Quraisy',4],[107,'الماعون','Al-Mā‘ūn',7],[108,'الكوثر','Al-Kauṡar',3],[109,'الكافرون','Al-Kāfirūn',6],
            [110,'النصر','An-Naṣr',3],[111,'المسد','Al-Masad',5],[112,'الإخلاص','Al-Ikhlāṣ',4],[113,'الفلق','Al-Falaq',5],[114,'الناس','An-Nās',6],
        ];
        foreach ($surahs as [$id,$arabic,$latin,$verses]) {
            QuranSurah::updateOrCreate(['id'=>$id], ['name_arabic'=>$arabic,'name_latin'=>$latin,'revelation_place'=>null,'verse_count'=>$verses,'sequence'=>$id]);
        }

        if (filter_var(env('SEED_DEMO_DATA', false), FILTER_VALIDATE_BOOL)) {
            $this->call(DemoDataSeeder::class);
        }
    }
}
