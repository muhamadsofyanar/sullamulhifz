<?php

namespace App\Console\Commands;

use App\Models\CommunicationDelivery;
use Illuminate\Console\Command;

class PruneCommunicationDeliveries extends Command
{
    protected $signature = 'sullam:prune-communications {--days= : Override masa retensi}';

    protected $description = 'Menghapus riwayat komunikasi selesai yang melewati masa retensi';

    public function handle(): int
    {
        $days = max(30, (int) ($this->option('days') ?: config('communications.retention_days', 365)));
        $deleted = CommunicationDelivery::query()
            ->whereIn('status', ['sent', 'delivered', 'received'])
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        $this->info("{$deleted} riwayat komunikasi selesai yang lebih lama dari {$days} hari dihapus.");

        return self::SUCCESS;
    }
}
