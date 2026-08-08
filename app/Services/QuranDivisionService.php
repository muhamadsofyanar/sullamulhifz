<?php

namespace App\Services;

use App\Models\QuranAyah;
use App\Models\QuranDivisionUnit;
use App\Models\QuranSurah;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class QuranDivisionService
{
    /** @return array<string,int> */
    public function sync(): array
    {
        if (! Schema::hasTable('quran_division_units') || ! Schema::hasTable('quran_ayahs')) {
            return ['juz' => 0, 'hizb' => 0, 'rubu' => 0, 'ruku' => 0, 'fami_manzil' => 0];
        }

        if (QuranAyah::query()->count() < QuranCorpusSyncService::AYAH_COUNT) {
            return ['juz' => 0, 'hizb' => 0, 'rubu' => 0, 'ruku' => 0, 'fami_manzil' => 0];
        }

        DB::transaction(function (): void {
            $this->syncGrouped('juz', 'juz_number', 'Juz');
            $this->syncGrouped('rubu', 'hizb_quarter', 'Rubu‘ al-Ḥizb');
            $this->syncHizb();
            $this->syncGrouped('ruku', 'ruku_number', 'Rukū‘');
            $this->syncFamiManzil();
        });

        return [
            'juz' => QuranDivisionUnit::where('unit_type', 'juz')->count(),
            'hizb' => QuranDivisionUnit::where('unit_type', 'hizb')->count(),
            'rubu' => QuranDivisionUnit::where('unit_type', 'rubu')->count(),
            'ruku' => QuranDivisionUnit::where('unit_type', 'ruku')->count(),
            'fami_manzil' => QuranDivisionUnit::where('unit_type', 'fami_manzil')->count(),
        ];
    }

    private function syncGrouped(string $type, string $column, string $labelPrefix): void
    {
        $numbers = QuranAyah::query()->whereNotNull($column)->distinct()->orderBy($column)->pluck($column);
        foreach ($numbers as $number) {
            $ayahs = QuranAyah::query()->where($column, $number)->orderBy('global_number')->get();
            $this->upsertUnit($type, (int) $number, $labelPrefix.' '.(int) $number, $ayahs, [
                'juz_number' => $type === 'juz' ? (int) $number : $ayahs->first()?->juz_number,
                'hizb_quarter' => $type === 'rubu' ? (int) $number : null,
            ]);
        }
    }

    private function syncHizb(): void
    {
        $quarters = QuranAyah::query()->whereNotNull('hizb_quarter')->distinct()->orderBy('hizb_quarter')->pluck('hizb_quarter');
        $hizbNumbers = $quarters->map(fn ($quarter): int => (int) ceil(((int) $quarter) / 4))->unique()->values();

        foreach ($hizbNumbers as $hizb) {
            $firstQuarter = (($hizb - 1) * 4) + 1;
            $lastQuarter = $hizb * 4;
            $ayahs = QuranAyah::query()
                ->whereBetween('hizb_quarter', [$firstQuarter, $lastQuarter])
                ->orderBy('global_number')
                ->get();
            $this->upsertUnit('hizb', $hizb, 'Ḥizb '.$hizb, $ayahs, [
                'juz_number' => $ayahs->first()?->juz_number,
            ]);
        }
    }

    private function syncFamiManzil(): void
    {
        $definitions = [
            [1, 'fa', 'Fa — Al-Fātiḥah sampai An-Nisā’', 1, 4],
            [2, 'mim', 'Mim — Al-Mā’idah sampai At-Taubah', 5, 9],
            [3, 'ya', 'Ya — Yūnus sampai An-Naḥl', 10, 16],
            [4, 'ba', 'Ba — Al-Isrā’ sampai Al-Furqān', 17, 25],
            [5, 'syin', 'Syin — Asy-Syu‘arā’ sampai Yāsīn', 26, 36],
            [6, 'wau', 'Wau — Aṣ-Ṣāffāt sampai Al-Ḥujurāt', 37, 49],
            [7, 'qaf', 'Qaf — Qāf sampai An-Nās', 50, 114],
        ];

        foreach ($definitions as [$number, $code, $label, $startSurah, $endSurah]) {
            $ayahs = QuranAyah::query()
                ->whereBetween('surah_id', [$startSurah, $endSurah])
                ->orderBy('global_number')
                ->get();
            $this->upsertUnit('fami_manzil', $number, $label, $ayahs, [
                'code' => $code,
                'description' => 'Pembagian tujuh manzil Fami Bisyauqin untuk tilawah atau murāja‘ah; porsi tidak harus sama jumlah halaman setiap harinya.',
            ]);
        }
    }

    /** @param Collection<int,QuranAyah> $ayahs @param array<string,mixed> $extra */
    private function upsertUnit(string $type, int $number, string $label, Collection $ayahs, array $extra = []): void
    {
        $first = $ayahs->first();
        $last = $ayahs->last();
        if (! $first || ! $last) {
            return;
        }

        QuranDivisionUnit::updateOrCreate(
            ['unit_type' => $type, 'unit_number' => $number],
            [
                'code' => $extra['code'] ?? null,
                'label' => $label,
                'start_global_number' => $first->global_number,
                'end_global_number' => $last->global_number,
                'start_surah_id' => $first->surah_id,
                'start_verse' => $first->verse_number,
                'end_surah_id' => $last->surah_id,
                'end_verse' => $last->verse_number,
                'juz_number' => $extra['juz_number'] ?? $first->juz_number,
                'hizb_quarter' => $extra['hizb_quarter'] ?? null,
                'description' => $extra['description'] ?? null,
            ],
        );
    }
}
