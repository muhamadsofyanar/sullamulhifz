<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class InfaqMonthlyReport extends Model
{
    protected $fillable = ['public_id', 'institution_id', 'period', 'snapshot', 'status', 'locked_by_user_id', 'locked_at', 'checksum'];
    protected function casts(): array { return ['snapshot' => 'array', 'locked_at' => 'datetime']; }
    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Laporan bulanan yang dikunci tidak dapat diubah.'));
        static::deleting(fn () => throw new LogicException('Laporan bulanan yang dikunci tidak dapat dihapus.'));
    }
}
