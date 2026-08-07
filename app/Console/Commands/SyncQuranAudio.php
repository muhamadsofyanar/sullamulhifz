<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Services\QuranAudioSyncService;
use App\Services\QuranCorpusSyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncQuranAudio extends Command
{
    protected $signature = 'sullam:sync-quran-audio {--institution=} {--force : Sinkronkan ulang walaupun data sudah lengkap}';
    protected $description = 'Sinkronisasi timing Full Qur’an 30 juz dan preset latihan dari MP3Quran.net';

    public function handle(QuranAudioSyncService $service, QuranCorpusSyncService $corpus): int
    {
        if (! $corpus->isComplete()) {
            $this->info('Menyiapkan korpus 114 surah / 6.236 ayat lebih dahulu...');
            try {
                $corpus->sync((bool) $this->option('force'));
            } catch (Throwable $e) {
                report($e);
                $this->error('Korpus Full Qur’an belum siap: '.$e->getMessage());
                return self::FAILURE;
            }
        }
        $query = Institution::query()->where('status', 'active');
        if ($id = $this->option('institution')) $query->whereKey($id);
        $institutions = $query->get();

        if ($institutions->isEmpty()) {
            $this->error('Lembaga aktif tidak ditemukan.');
            return self::FAILURE;
        }

        foreach ($institutions as $institution) {
            $this->info('Sinkronisasi '.$institution->name.'...');
            try {
                $result = $service->syncInstitution($institution);
                $this->table(['Komponen','Jumlah'], [
                    ['Timing qari utama', $result['timings'].'/6236'],
                    ['Total timing dua qari', $result['total_timings'].'/'.$result['expected_timings']],
                    ['Halaman Mushaf', $result['pages']],
                    ['Preset latihan', $result['presets']],
                    ['Surah gagal', count($result['failed_surahs'])],
                ]);
                if ($result['failed_surahs']) {
                    $this->warn('Perlu sinkronisasi ulang untuk surah: '.implode(', ', $result['failed_surahs']));
                }
            } catch (Throwable $e) {
                report($e);
                $this->error($e->getMessage());
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
