<?php

namespace App\Console\Commands;

use App\Models\MediaAsset;
use App\Services\MediaStorageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class PurgeExpiredMedia extends Command
{
    protected $signature = 'sullam:purge-expired-media {--dry-run : Hanya tampilkan jumlah file}';
    protected $description = 'Menghapus media yang melewati masa simpan dan tidak lagi dibutuhkan';

    public function handle(MediaStorageService $media): int
    {
        if (! Schema::hasTable('media_assets')) {
            $this->warn('Tabel media_assets belum tersedia.');
            return self::SUCCESS;
        }

        $query = MediaAsset::query()
            ->whereNotNull('retention_until')
            ->where('retention_until', '<=', now())
            ->whereDoesntHave('links');

        $count = (clone $query)->count();
        if ($this->option('dry-run')) {
            $this->info("{$count} media kedaluwarsa dapat dihapus.");
            return self::SUCCESS;
        }

        $deleted = 0;
        $query->orderBy('id')->chunkById(100, function ($assets) use ($media, &$deleted): void {
            foreach ($assets as $asset) {
                $media->delete($asset);
                $deleted++;
            }
        });

        $this->info("{$deleted} media kedaluwarsa berhasil dihapus.");
        return self::SUCCESS;
    }
}
