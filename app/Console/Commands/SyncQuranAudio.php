<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Services\QuranAudioSyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncQuranAudio extends Command
{
    protected $signature = 'sullam:sync-quran-audio {--institution=} {--force : Sinkronkan ulang walaupun data sudah lengkap}';
    protected $description = 'Sinkronisasi timing ayat Juz 30 dan contoh latihan dari MP3Quran.net';

    public function handle(QuranAudioSyncService $service): int
    {
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
                    ['Timing ayat tersimpan', $result['timings']],
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
