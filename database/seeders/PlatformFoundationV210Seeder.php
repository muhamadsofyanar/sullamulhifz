<?php

namespace Database\Seeders;

use App\Models\AcademicPeriod;
use App\Models\Branch;
use App\Models\FeatureFlag;
use App\Models\Institution;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlatformFoundationV210Seeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'superadmin' => ['Superadmin', 'system'],
            'institution_admin' => ['Admin Lembaga', 'institution'],
            'head' => ['Kepala TPA', 'institution'],
            'teacher' => ['Guru', 'institution'],
            'guardian' => ['Orang Tua/Wali', 'institution'],
            'student' => ['Santri', 'institution'],
        ];

        foreach ($roles as $name => [$displayName, $scope]) {
            Role::updateOrCreate(
                ['name' => $name],
                ['display_name' => $displayName, 'scope' => $scope],
            );
        }

        $permissions = [
            'dashboard.view' => 'Melihat dashboard',
            'institution.view' => 'Melihat profil lembaga',
            'institution.manage' => 'Mengelola profil lembaga',
            'academic.view' => 'Melihat data akademik',
            'academic.manage' => 'Mengelola data akademik',
            'students.view' => 'Melihat data santri',
            'students.manage' => 'Mengelola data santri',
            'teachers.view' => 'Melihat data guru',
            'teachers.manage' => 'Mengelola data guru',
            'guardians.view' => 'Melihat data wali',
            'guardians.manage' => 'Mengelola data wali',
            'meetings.view' => 'Melihat pertemuan',
            'meetings.manage' => 'Mengelola pertemuan',
            'attendance.view' => 'Melihat absensi',
            'attendance.manage' => 'Mengelola absensi',
            'learning.view' => 'Melihat catatan pembelajaran',
            'learning.manage' => 'Mengelola catatan pembelajaran',
            'assignments.view' => 'Melihat tugas',
            'assignments.manage' => 'Membuat dan mengelola tugas',
            'assignments.review' => 'Memeriksa bukti tugas',
            'assignments.submit' => 'Mengirim bukti tugas',
            'liaison.view' => 'Melihat buku penghubung',
            'liaison.manage' => 'Mengelola buku penghubung',
            'announcements.view' => 'Melihat pengumuman',
            'announcements.manage' => 'Mengelola pengumuman',
            'friday.view' => 'Melihat Pembinaan Jumat',
            'friday.manage' => 'Mengelola Pembinaan Jumat',
            'reports.view' => 'Melihat laporan',
            'reports.export' => 'Mengekspor laporan',
            'media.view' => 'Melihat media sesuai lingkup',
            'media.manage' => 'Mengelola media',
            'academy.view' => 'Mengakses Academy',
            'academy.manage' => 'Mengelola Academy',
            'quran.view' => 'Mengakses latihan Al-Qur’an',
            'quran.manage' => 'Mengelola pustaka Al-Qur’an',
            'website.manage' => 'Mengelola website publik',
            'report_cards.view' => 'Melihat rapor',
            'report_cards.manage' => 'Mengelola rapor',
            'admissions.manage' => 'Mengelola pendaftaran',
            'audit.view' => 'Melihat audit aktivitas',
            'backup.view' => 'Melihat status backup',
            'backup.manage' => 'Mengelola backup',
            'features.manage' => 'Mengelola fitur lembaga',
            'permissions.manage' => 'Mengelola peran dan izin',
        ];

        foreach ($permissions as $name => $displayName) {
            Permission::updateOrCreate(
                ['name' => $name],
                ['display_name' => $displayName],
            );
        }

        $all = array_keys($permissions);
        $roleMap = [
            'superadmin' => $all,
            'institution_admin' => array_values(array_diff($all, ['backup.manage'])),
            'head' => [
                'dashboard.view', 'institution.view', 'academic.view', 'students.view', 'teachers.view', 'guardians.view',
                'meetings.view', 'attendance.view', 'learning.view', 'assignments.view', 'assignments.review',
                'liaison.view', 'liaison.manage', 'announcements.view', 'announcements.manage', 'friday.view', 'friday.manage',
                'reports.view', 'reports.export', 'media.view', 'academy.view', 'academy.manage', 'quran.view',
                'report_cards.view', 'report_cards.manage', 'backup.view',
            ],
            'teacher' => [
                'dashboard.view', 'institution.view', 'academic.view', 'students.view', 'meetings.view', 'meetings.manage',
                'attendance.view', 'attendance.manage', 'learning.view', 'learning.manage', 'assignments.view',
                'assignments.manage', 'assignments.review', 'liaison.view', 'liaison.manage', 'announcements.view',
                'friday.view', 'friday.manage', 'reports.view', 'media.view', 'academy.view', 'quran.view',
            ],
            'guardian' => [
                'dashboard.view', 'institution.view', 'students.view', 'attendance.view', 'learning.view',
                'assignments.view', 'assignments.submit', 'liaison.view', 'liaison.manage', 'announcements.view',
                'friday.view', 'reports.view', 'media.view', 'academy.view', 'quran.view', 'report_cards.view',
            ],
            'student' => ['dashboard.view', 'learning.view', 'assignments.view', 'announcements.view', 'friday.view', 'academy.view', 'quran.view'],
        ];

        foreach ($roleMap as $roleName => $permissionNames) {
            $role = Role::where('name', $roleName)->firstOrFail();
            $permissionIds = Permission::whereIn('name', $permissionNames)->pluck('id');
            $role->permissions()->syncWithoutDetaching($permissionIds);
        }

        if (! Schema::hasTable('branches')) {
            return;
        }

        foreach (Institution::query()->get() as $institution) {
            $branch = Branch::updateOrCreate(
                ['institution_id' => $institution->id, 'code' => 'UTAMA'],
                ['name' => 'Cabang Utama', 'status' => 'active', 'is_main' => true],
            );

            foreach (['students', 'classes', 'learning_groups', 'schedules', 'user_roles'] as $tableName) {
                if (Schema::hasColumn($tableName, 'branch_id')) {
                    DB::table($tableName)
                        ->where('institution_id', $institution->id)
                        ->whereNull('branch_id')
                        ->update(['branch_id' => $branch->id]);
                }
            }

            foreach ([
                'core_academic' => true,
                'quran_audio' => true,
                'parent_academy' => true,
                'public_website' => true,
                'report_cards' => true,
                'admissions' => true,
                'community' => false,
                'multi_branch' => false,
            ] as $featureKey => $enabled) {
                FeatureFlag::updateOrCreate(
                    ['institution_id' => $institution->id, 'feature_key' => $featureKey],
                    ['enabled' => $enabled],
                );
            }

            foreach ($institution->academicYears as $year) {
                AcademicPeriod::updateOrCreate(
                    ['academic_year_id' => $year->id, 'name' => 'Periode Utama'],
                    [
                        'start_date' => $year->start_date,
                        'end_date' => $year->end_date,
                        'status' => $year->is_active ? 'active' : 'closed',
                    ],
                );
            }
        }
    }
}
