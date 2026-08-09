<?php

namespace App\Support;

/** @phase 4.5 Personal 2.0 — every person, every aspiration */
class PersonalIdentity
{
    public static function ageGroups(): array
    {
        return [
            'child' => 'Anak (di bawah 13 tahun)',
            'teen' => 'Remaja (13–17 tahun)',
            'young_adult' => 'Dewasa muda (18–25 tahun)',
            'adult' => 'Dewasa (26–59 tahun)',
            'senior' => 'Lanjut usia (60 tahun ke atas)',
        ];
    }

    public static function interests(): array
    {
        return [
            'healthcare' => 'Kesehatan & kedokteran',
            'education' => 'Pendidikan & keguruan',
            'aviation_transport' => 'Penerbangan & transportasi',
            'agriculture_environment' => 'Tanaman, pertanian & lingkungan',
            'communication_media' => 'Komunikasi & media',
            'technology_engineering' => 'Teknologi & rekayasa',
            'business_entrepreneurship' => 'Bisnis & kewirausahaan',
            'arts_creativity' => 'Seni & kreativitas',
            'public_service' => 'Pelayanan masyarakat',
            'quran_religious_studies' => 'Al-Qur’an & ilmu agama',
            'sports' => 'Olahraga',
            'other' => 'Minat lainnya',
        ];
    }

    public static function learningModes(): array
    {
        return [
            'self' => [
                'label' => 'Mandiri',
                'description' => 'Menjaga ritme sendiri di Ruang Personal yang privat.',
            ],
            'with_parent' => [
                'label' => 'Bersama orang tua/wali',
                'description' => 'Didampingi keluarga dengan hubungan dan batas akses yang jelas.',
            ],
            'private_teacher' => [
                'label' => 'Bersama ustadz privat',
                'description' => 'Menerima arahan, setoran, dan koreksi dari pendamping pilihan.',
            ],
            'institution' => [
                'label' => 'Melalui lembaga',
                'description' => 'Belajar dalam TPA, sekolah, pesantren, kampus, atau komunitas.',
            ],
        ];
    }

    public static function portfolioCategories(): array
    {
        return [
            'quran_character' => 'Karakter Qur’ani',
            'learning' => 'Pembelajaran',
            'service' => 'Pelayanan & kepedulian',
            'skill' => 'Keterampilan',
            'project' => 'Karya atau proyek',
            'reflection' => 'Refleksi pertumbuhan',
        ];
    }

    public static function isMinor(?string $ageGroup): bool
    {
        return in_array($ageGroup, ['child', 'teen'], true);
    }
}
