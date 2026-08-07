<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\ClassEnrollment;
use App\Models\GroupMembership;
use App\Models\Guardian;
use App\Models\Institution;
use App\Models\LearningGroup;
use App\Models\Program;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TeacherAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class InitialTpaDataSeeder extends Seeder
{
    public function run(): void
    {
        $dataPath = database_path('data/initial_tpa_2026_2027.enc.json');

        if (! is_file($dataPath)) {
            throw new RuntimeException("Data awal terenkripsi tidak ditemukan: {$dataPath}");
        }

        $encodedKey = env('INITIAL_TPA_DATA_KEY');

        if (! is_string($encodedKey) || $encodedKey === '') {
            throw new RuntimeException(
                'INITIAL_TPA_DATA_KEY belum diisi pada Environment Variables.'
            );
        }

        $key = base64_decode($encodedKey, true);

        if (! is_string($key) || strlen($key) !== 32) {
            throw new RuntimeException(
                'INITIAL_TPA_DATA_KEY tidak valid. Gunakan key base64 32-byte dari paket rilis.'
            );
        }

        $payload = json_decode((string) file_get_contents($dataPath), true, 512, JSON_THROW_ON_ERROR);
        $nonce = base64_decode((string) ($payload['nonce'] ?? ''), true);
        $tag = base64_decode((string) ($payload['tag'] ?? ''), true);
        $ciphertext = base64_decode((string) ($payload['ciphertext'] ?? ''), true);
        $aad = base64_decode((string) ($payload['aad'] ?? ''), true);

        if (! is_string($nonce) || ! is_string($tag) || ! is_string($ciphertext) || ! is_string($aad)) {
            throw new RuntimeException('Payload data awal terenkripsi rusak atau tidak lengkap.');
        }

        $plainJson = openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            $aad
        );

        if (! is_string($plainJson)) {
            throw new RuntimeException(
                'Gagal membuka data awal. Periksa INITIAL_TPA_DATA_KEY.'
            );
        }

        /** @var array<string, mixed> $data */
        $data = json_decode($plainJson, true, 512, JSON_THROW_ON_ERROR);

        $institution = Institution::where(
            'code',
            env('INITIAL_INSTITUTION_CODE', 'ALINSYIRAH')
        )->firstOrFail();

        $academicYear = AcademicYear::where('institution_id', $institution->id)
            ->where('name', $data['academic_year'])
            ->firstOrFail();

        $teacherRole = Role::where('name', 'teacher')->firstOrFail();
        $guardianRole = Role::where('name', 'guardian')->firstOrFail();

        $teacherPassword = $this->requiredPassword('INITIAL_TEACHER_PASSWORD');
        $guardianPassword = $this->requiredPassword('INITIAL_GUARDIAN_PASSWORD');

        $classes = $data['classes'];
        $expectedClassCounts = $data['class_counts'];

        foreach ($expectedClassCounts as $classCode => $expectedCount) {
            $actualCount = count($classes[$classCode] ?? []);

            if ($actualCount !== $expectedCount) {
                throw new RuntimeException(
                    "Jumlah santri {$classCode} tidak sesuai: {$actualCount}, seharusnya {$expectedCount}."
                );
            }
        }

        if (array_sum(array_map('count', $classes)) !== 88) {
            throw new RuntimeException('Jumlah keseluruhan santri harus tepat 88.');
        }

        foreach ($data['teachers'] as $definition) {
            $user = User::where('email', $definition['email'])->first();

            if (! $user) {
                $user = User::create([
                    'institution_id' => $institution->id,
                    'name' => $definition['name'],
                    'email' => $definition['email'],
                    'phone' => $definition['phone'],
                    'password' => Hash::make($teacherPassword),
                    'status' => 'active',
                    'must_change_password' => true,
                    'email_verified_at' => now(),
                ]);
            } else {
                $user->update([
                    'institution_id' => $institution->id,
                    'name' => $definition['name'],
                    'phone' => $definition['phone'],
                    'status' => 'active',
                ]);
            }

            $user->roles()->syncWithoutDetaching([
                $teacherRole->id => [
                    'institution_id' => $institution->id,
                    'status' => 'active',
                ],
            ]);

            $teacher = Teacher::updateOrCreate(
                [
                    'institution_id' => $institution->id,
                    'employee_code' => $definition['code'],
                ],
                [
                    'user_id' => $user->id,
                    'full_name' => $definition['name'],
                    'nickname' => $definition['name'],
                    'phone' => $definition['phone'],
                    'email' => $definition['email'],
                    'joined_at' => '2026-07-01',
                    'specialization' => $definition['specialization'],
                    'status' => 'active',
                ]
            );

            foreach ($definition['class_assignments'] as $assignment) {
                $class = SchoolClass::where('academic_year_id', $academicYear->id)
                    ->where('code', $assignment['class'])
                    ->firstOrFail();

                $program = Program::where('institution_id', $institution->id)
                    ->where('code', $assignment['program'])
                    ->firstOrFail();

                TeacherAssignment::updateOrCreate(
                    [
                        'institution_id' => $institution->id,
                        'academic_year_id' => $academicYear->id,
                        'teacher_id' => $teacher->id,
                        'class_id' => $class->id,
                        'learning_group_id' => null,
                        'program_id' => $program->id,
                    ],
                    [
                        'assignment_role' => 'lead',
                        'valid_from' => '2026-07-01',
                        'valid_until' => '2027-06-30',
                        'status' => 'active',
                        'notes' => str_starts_with($assignment['class'], 'TAMHIDI')
                            ? 'KBM pukul 13.00–15.00 WIB. Hari pertemuan mengikuti kalender TPA.'
                            : 'KBM pukul 15.30–17.30 WIB. Hari pertemuan mengikuti kalender TPA.',
                    ]
                );
            }

            foreach ($definition['group_assignments'] as $assignment) {
                $group = LearningGroup::where('academic_year_id', $academicYear->id)
                    ->where('code', $assignment['group'])
                    ->firstOrFail();

                $program = Program::where('institution_id', $institution->id)
                    ->where('code', $assignment['program'])
                    ->firstOrFail();

                TeacherAssignment::updateOrCreate(
                    [
                        'institution_id' => $institution->id,
                        'academic_year_id' => $academicYear->id,
                        'teacher_id' => $teacher->id,
                        'class_id' => null,
                        'learning_group_id' => $group->id,
                        'program_id' => $program->id,
                    ],
                    [
                        'assignment_role' => 'lead',
                        'valid_from' => '2026-07-01',
                        'valid_until' => '2027-06-30',
                        'status' => 'active',
                        'notes' => 'Kelas Tahfizh gabungan berdasarkan kelompok A/B.',
                    ]
                );
            }
        }

        $sequence = 1;

        foreach ($classes as $classCode => $studentNames) {
            $class = SchoolClass::where('academic_year_id', $academicYear->id)
                ->where('code', $classCode)
                ->firstOrFail();

            foreach ($studentNames as $studentName) {
                $number = str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
                $studentCode = 'ALINSY-2026-'.$number;
                $guardianEmail = 'wali'.$number.'@taysriulqurani.id';
                $guardianPhone = '628000000'.$number;

                $guardianUser = User::where('email', $guardianEmail)->first();

                if (! $guardianUser) {
                    $guardianUser = User::create([
                        'institution_id' => $institution->id,
                        'name' => 'Wali '.$studentName,
                        'email' => $guardianEmail,
                        'phone' => $guardianPhone,
                        'password' => Hash::make($guardianPassword),
                        'status' => 'active',
                        'must_change_password' => true,
                        'email_verified_at' => now(),
                    ]);
                } else {
                    $guardianUser->update([
                        'institution_id' => $institution->id,
                        'name' => 'Wali '.$studentName,
                        'phone' => $guardianPhone,
                        'status' => 'active',
                    ]);
                }

                $guardianUser->roles()->syncWithoutDetaching([
                    $guardianRole->id => [
                        'institution_id' => $institution->id,
                        'status' => 'active',
                    ],
                ]);

                $guardian = Guardian::updateOrCreate(
                    [
                        'institution_id' => $institution->id,
                        'email' => $guardianEmail,
                    ],
                    [
                        'user_id' => $guardianUser->id,
                        'full_name' => 'Wali '.$studentName,
                        'phone' => $guardianPhone,
                        'status' => 'active',
                    ]
                );

                $nameParts = preg_split('/\s+/', trim($studentName)) ?: [];
                $nickname = $nameParts[0] ?? $studentName;

                if (in_array($nickname, ['M.', 'Muhammad', 'Muhamad'], true) && isset($nameParts[1])) {
                    $nickname = $nameParts[1];
                }

                $student = Student::updateOrCreate(
                    [
                        'institution_id' => $institution->id,
                        'student_code' => $studentCode,
                    ],
                    [
                        'full_name' => $studentName,
                        'nickname' => $nickname,
                        'joined_at' => '2026-07-01',
                        'status' => 'active',
                        'stifin_status' => 'untested',
                    ]
                );

                $student->guardians()->syncWithoutDetaching([
                    $guardian->id => [
                        'relationship' => 'guardian',
                        'is_primary_contact' => true,
                        'can_receive_notifications' => true,
                        'can_submit_assignments' => true,
                        'can_view_learning_records' => true,
                        'started_at' => '2026-07-01',
                    ],
                ]);

                ClassEnrollment::updateOrCreate(
                    [
                        'class_id' => $class->id,
                        'student_id' => $student->id,
                        'academic_year_id' => $academicYear->id,
                    ],
                    [
                        'enrolled_at' => '2026-07-01',
                        'status' => 'active',
                    ]
                );

                $groupCode = null;

                foreach ($data['tahfizh_membership'] as $candidateGroup => $sourceClasses) {
                    if (in_array($classCode, $sourceClasses, true)) {
                        $groupCode = $candidateGroup;
                        break;
                    }
                }

                if ($groupCode) {
                    $group = LearningGroup::where('academic_year_id', $academicYear->id)
                        ->where('code', $groupCode)
                        ->firstOrFail();

                    GroupMembership::updateOrCreate(
                        [
                            'learning_group_id' => $group->id,
                            'student_id' => $student->id,
                        ],
                        [
                            'joined_at' => '2026-07-01',
                            'status' => 'active',
                            'notes' => 'Pembagian awal Tahfizh Tahun Ajaran 2026/2027.',
                        ]
                    );
                }

                $sequence++;
            }
        }

        $studentCount = Student::where('institution_id', $institution->id)
            ->where('status', 'active')
            ->count();

        $guardianCount = Guardian::where('institution_id', $institution->id)
            ->where('status', 'active')
            ->count();

        $teacherCount = Teacher::where('institution_id', $institution->id)
            ->where('status', 'active')
            ->count();

        $tahfizhACount = LearningGroup::where('academic_year_id', $academicYear->id)
            ->where('code', 'TAHFIZH-A')
            ->firstOrFail()
            ->activeMemberships()
            ->count();

        $tahfizhBCount = LearningGroup::where('academic_year_id', $academicYear->id)
            ->where('code', 'TAHFIZH-B')
            ->firstOrFail()
            ->activeMemberships()
            ->count();

        if ($studentCount < 88 || $guardianCount < 88 || $teacherCount < 4) {
            throw new RuntimeException(
                "Verifikasi data awal gagal: santri={$studentCount}, wali={$guardianCount}, guru={$teacherCount}."
            );
        }

        if ($tahfizhACount !== 30 || $tahfizhBCount !== 27) {
            throw new RuntimeException(
                "Verifikasi kelas Tahfizh gagal: A={$tahfizhACount}, B={$tahfizhBCount}."
            );
        }

        $this->command?->info(
            "Data awal TPA selesai: 88 santri, 88 wali, 4 guru, Tahfizh A={$tahfizhACount}, Tahfizh B={$tahfizhBCount}."
        );
    }

    private function requiredPassword(string $key): string
    {
        $password = (string) env($key, '');

        if ($password === '' || strlen($password) < 12
            || ! preg_match('/[A-Z]/', $password)
            || ! preg_match('/[a-z]/', $password)
            || ! preg_match('/[0-9]/', $password)) {
            throw new RuntimeException("{$key} wajib diisi, minimal 12 karakter, serta memuat huruf besar, huruf kecil, dan angka sebelum seeder data awal dijalankan.");
        }

        return $password;
    }

}
