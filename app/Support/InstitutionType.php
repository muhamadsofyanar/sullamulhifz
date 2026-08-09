<?php

namespace App\Support;

/** @phase 4.4 Multi-tenant Institution Foundation */

final class InstitutionType
{
    public static function catalog(): array
    {
        return [
            'tpa' => ['label' => 'TPA / TPQ', 'student' => 'Santri', 'teacher' => 'Ustadz/Ustadzah', 'guardian' => 'Orang Tua/Wali'],
            'rumah_tahfiz' => ['label' => 'Rumah Tahfiz', 'student' => 'Santri', 'teacher' => 'Ustadz/Ustadzah', 'guardian' => 'Orang Tua/Wali'],
            'pesantren' => ['label' => 'Pesantren', 'student' => 'Santri', 'teacher' => 'Ustadz/Ustadzah', 'guardian' => 'Wali Santri'],
            'sd' => ['label' => 'Sekolah Dasar', 'student' => 'Siswa', 'teacher' => 'Guru', 'guardian' => 'Orang Tua/Wali'],
            'smp' => ['label' => 'Sekolah Menengah Pertama', 'student' => 'Siswa', 'teacher' => 'Guru', 'guardian' => 'Orang Tua/Wali'],
            'sma' => ['label' => 'Sekolah Menengah Atas', 'student' => 'Siswa', 'teacher' => 'Guru', 'guardian' => 'Orang Tua/Wali'],
            'kampus' => ['label' => 'Kampus / Perguruan Tinggi', 'student' => 'Mahasiswa', 'teacher' => 'Dosen/Ustadz Pembimbing', 'guardian' => 'Pendamping'],
            'komunitas' => ['label' => 'Komunitas', 'student' => 'Peserta', 'teacher' => 'Pembimbing', 'guardian' => 'Pendamping'],
            'personal' => ['label' => 'Personal', 'student' => 'Pembelajar', 'teacher' => 'Ustadz Pembimbing', 'guardian' => 'Pendamping'],
        ];
    }

    public static function keys(bool $includePersonal = false): array
    {
        $keys = array_keys(self::catalog());

        return $includePersonal ? $keys : array_values(array_diff($keys, ['personal']));
    }

    public static function terminology(string $type): array
    {
        return self::catalog()[$type] ?? self::catalog()['komunitas'];
    }

    public static function label(string $type): string
    {
        return self::terminology($type)['label'];
    }
}
