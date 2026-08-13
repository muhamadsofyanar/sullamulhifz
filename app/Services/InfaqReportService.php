<?php

namespace App\Services;

use App\Models\InfaqLedgerEntry;
use App\Models\InfaqMonthlyReport;
use App\Models\InfaqRealisation;
use App\Models\InfaqTransaction;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InfaqReportService
{
    /** @return array<string,mixed> */
    public function snapshot(int $institutionId, string $period): array
    {
        [$year, $month] = array_map('intval', explode('-', $period));
        $range = [now()->setDate($year, $month, 1)->startOfMonth(), now()->setDate($year, $month, 1)->endOfMonth()];
        $ledger = InfaqLedgerEntry::query()->where('institution_id', $institutionId)->whereBetween('occurred_at', $range)
            ->selectRaw('category, entry_type, SUM(amount) as total')->groupBy('category', 'entry_type')->orderBy('category')->get();
        $realisations = InfaqRealisation::query()->where('institution_id', $institutionId)->where('status', 'verified')
            ->whereBetween('realised_on', [$range[0]->toDateString(), $range[1]->toDateString()]);

        return [
            'period' => $period,
            'received' => (string) InfaqTransaction::query()->where('institution_id', $institutionId)->where('status', 'verified')->whereBetween('verified_at', $range)->sum('amount'),
            'ledger' => $ledger->toArray(),
            'realised' => (string) $realisations->sum('amount'),
            'programs' => (clone $realisations)->count(),
            'beneficiaries' => (int) (clone $realisations)->sum('beneficiary_count'),
            'generated_at' => now()->toIso8601String(),
        ];
    }

    public function lock(User $actor, string $period): InfaqMonthlyReport
    {
        abort_unless(preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period) === 1, 422, 'Periode laporan tidak valid.');

        return DB::transaction(function () use ($actor, $period): InfaqMonthlyReport {
            Institution::query()->whereKey($actor->institution_id)->lockForUpdate()->firstOrFail();
            $existing = InfaqMonthlyReport::query()->where('institution_id', $actor->institution_id)->where('period', $period)->first();
            if ($existing) {
                return $existing;
            }
            $snapshot = $this->snapshot((int) $actor->institution_id, $period);

            return InfaqMonthlyReport::create([
                'institution_id' => $actor->institution_id, 'period' => $period,
                'public_id' => (string) Str::uuid(), 'snapshot' => $snapshot, 'status' => 'locked',
                'locked_by_user_id' => $actor->id, 'locked_at' => now(), 'checksum' => self::checksum($snapshot),
            ]);
        }, 3);
    }

    /** @param array<string,mixed> $snapshot */
    public static function checksum(array $snapshot): string
    {
        $json = json_encode(self::canonicalize($snapshot), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

        return hash('sha256', $json);
    }

    /** @param array<mixed> $value @return array<mixed> */
    private static function canonicalize(array $value): array
    {
        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = self::canonicalize($item);
            }
        }
        if (! array_is_list($value)) {
            ksort($value);
        }

        return $value;
    }
}
