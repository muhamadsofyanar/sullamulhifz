<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\MarhalahType;
use App\Models\MemorizationTarget;
use App\Models\QuranAyah;
use App\Models\QuranJourneyPortion;
use App\Models\QuranJourneyProfile;
use App\Models\QuranMushafLine;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Throwable;
use ZipArchive;

class MushafLineService
{
    public const DEFAULT_LAYOUT = 'kfgqpc-v2-1421h';
    public const TOTAL_PAGES = 604;
    public const STANDARD_LINE_SLOTS = 15;

    public function layoutCode(): string
    {
        return (string) config('sullam.mushaf_line_layout', self::DEFAULT_LAYOUT);
    }

    /** @return array{pages:int,lines:int,complete:bool,layout:string,source:string} */
    public function status(): array
    {
        if (! Schema::hasTable('quran_mushaf_lines')) {
            return ['pages'=>0,'lines'=>0,'complete'=>false,'layout'=>$this->layoutCode(),'source'=>$this->sourceName()];
        }

        $query = QuranMushafLine::query()->where('layout_code', $this->layoutCode());
        $pages = (clone $query)->distinct()->count('page_number');
        $lines = (clone $query)->count();

        return [
            'pages'=>$pages,
            'lines'=>$lines,
            'complete'=>$pages >= self::TOTAL_PAGES,
            'layout'=>$this->layoutCode(),
            'source'=>$this->sourceName(),
        ];
    }

    /** @return array{pages:int,lines:int,complete:bool,layout:string,source:string,changed:bool,method:string} */
    public function sync(bool $force = false): array
    {
        $before = $this->status();
        if ($before['complete'] && ! $force) {
            return $before + ['changed'=>false,'method'=>'already-complete'];
        }

        $method = 'archive';
        try {
            $this->syncArchive($force);
        } catch (Throwable $archiveError) {
            report($archiveError);
            $method = 'page-fallback';
            $this->syncPages($force);
        }

        $after = $this->status();
        return $after + ['changed'=>$after['lines'] !== $before['lines'] || $after['pages'] !== $before['pages'],'method'=>$method];
    }

    private function syncArchive(bool $force): void
    {
        $url = (string) config('sullam.mushaf_line_archive_url');
        if ($url === '') {
            throw new \RuntimeException('URL arsip Mushaf Line belum dikonfigurasi.');
        }

        $response = Http::connectTimeout(15)->timeout(180)->retry(3, 1500)->get($url);
        if (! $response->successful() || strlen($response->body()) < 1024) {
            throw new \RuntimeException('Arsip Mushaf Line tidak dapat diunduh. HTTP '.$response->status().'.');
        }

        $tmp = tempnam(sys_get_temp_dir(), 'sullam-mushaf-');
        if ($tmp === false) {
            throw new \RuntimeException('Tidak dapat membuat file sementara untuk arsip Mushaf Line.');
        }
        file_put_contents($tmp, $response->body());

        $zip = new ZipArchive();
        if ($zip->open($tmp) !== true) {
            @unlink($tmp);
            throw new \RuntimeException('Arsip Mushaf Line bukan ZIP yang valid.');
        }

        $seen = 0;
        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = (string) $zip->getNameIndex($i);
                if (! preg_match('~/mushaf/page-(\d{1,3})\.json$~', $name, $match)) {
                    continue;
                }
                $page = (int) $match[1];
                if ($page < 1 || $page > self::TOTAL_PAGES) {
                    continue;
                }
                if (! $force && QuranMushafLine::query()->where('layout_code',$this->layoutCode())->where('page_number',$page)->exists()) {
                    $seen++;
                    continue;
                }
                $json = $zip->getFromIndex($i);
                if (! is_string($json) || $json === '') {
                    throw new \RuntimeException('File halaman '.$page.' kosong di dalam arsip.');
                }
                $payload = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
                $this->importPage($payload, $page);
                $seen++;
            }
        } finally {
            $zip->close();
            @unlink($tmp);
        }

        if ($seen < self::TOTAL_PAGES) {
            throw new \RuntimeException('Arsip hanya memuat '.$seen.'/'.self::TOTAL_PAGES.' halaman Mushaf.');
        }
    }

    private function syncPages(bool $force): void
    {
        $pages = collect(range(1, self::TOTAL_PAGES));
        if (! $force) {
            $existing = QuranMushafLine::query()->where('layout_code',$this->layoutCode())->distinct()->pluck('page_number');
            $pages = $pages->diff($existing)->values();
        }

        foreach ($pages as $page) {
            $this->syncPage((int) $page, true);
        }
    }

    /** @param array<string,mixed> $payload */
    public function importPage(array $payload, int $expectedPage): void
    {
        $page = (int) ($payload['page'] ?? 0);
        $lines = $payload['lines'] ?? null;
        if ($page !== $expectedPage || ! is_array($lines) || $lines === []) {
            throw new \RuntimeException('Payload layout halaman '.$expectedPage.' tidak lengkap.');
        }

        $normalized = collect($lines)->map(function ($line) use ($page): array {
            if (! is_array($line)) {
                throw new \RuntimeException('Baris layout halaman '.$page.' tidak valid.');
            }
            $lineNumber = (int) ($line['line'] ?? $line['line_number'] ?? 0);
            if ($lineNumber < 1 || $lineNumber > self::STANDARD_LINE_SLOTS) {
                throw new \RuntimeException('Nomor baris halaman '.$page.' di luar 1–15.');
            }

            $rawType = (string) ($line['type'] ?? $line['line_type'] ?? '');
            $type = match ($rawType) {
                'text', 'ayah' => 'ayah',
                'surah-header', 'surah_name', 'surah-name' => 'surah_name',
                'basmala', 'basmallah', 'basmalah', 'bismillah' => 'basmallah',
                default => throw new \RuntimeException('Tipe baris '.$rawType.' tidak dikenal pada halaman '.$page.'.'),
            };

            $words = is_array($line['words'] ?? null) ? array_values(array_filter($line['words'], 'is_array')) : [];
            $firstLocation = $words ? (string) ($words[0]['location'] ?? '') : null;
            $lastLocation = $words ? (string) ($words[array_key_last($words)]['location'] ?? '') : null;
            [$fs,$fv,$fw] = $this->parseLocation($firstLocation);
            [$ls,$lv,$lw] = $this->parseLocation($lastLocation);

            $qpcV2 = null;
            if ($type === 'ayah' && $words) {
                $qpcV2 = trim(implode(' ', array_values(array_filter(array_map(
                    static fn(array $word): string => trim((string)($word['qpcV2'] ?? $word['qpc_v2'] ?? '')),
                    $words,
                )))));
            } elseif ($type === 'basmallah') {
                $qpcV2 = (string) ($line['qpcV2'] ?? $line['qpc_v2'] ?? '');
            }

            return [
                'layout_code'=>$this->layoutCode(),
                'page_number'=>$page,
                'line_number'=>$lineNumber,
                'line_type'=>$type,
                'is_centered'=>$type !== 'ayah',
                'text'=>$type === 'surah_name' ? (string)($line['text'] ?? '') : ($type === 'ayah' ? (string)($line['text'] ?? '') : null),
                'qpc_v2'=>$qpcV2 ?: null,
                'surah_number'=>isset($line['surah']) ? (int)$line['surah'] : (isset($line['surah_number']) ? (int)$line['surah_number'] : null),
                'verse_range'=>isset($line['verseRange']) ? (string)$line['verseRange'] : (isset($line['verse_range']) ? (string)$line['verse_range'] : null),
                'first_word_location'=>$firstLocation ?: null,
                'last_word_location'=>$lastLocation ?: null,
                'first_surah_id'=>$fs,
                'first_verse'=>$fv,
                'first_word_position'=>$fw,
                'last_surah_id'=>$ls,
                'last_verse'=>$lv,
                'last_word_position'=>$lw,
                'source_name'=>$this->sourceName(),
                'source_ref'=>(string) config('sullam.mushaf_line_source_ref', 'main'),
                'created_at'=>now(),
                'updated_at'=>now(),
            ];
        });

        if ($normalized->pluck('line_number')->unique()->count() !== $normalized->count()) {
            throw new \RuntimeException('Nomor baris ganda pada halaman '.$page.'.');
        }

        DB::transaction(function () use ($page, $normalized): void {
            QuranMushafLine::query()->where('layout_code',$this->layoutCode())->where('page_number',$page)->delete();
            foreach ($normalized->chunk(100) as $chunk) {
                QuranMushafLine::query()->insert($chunk->values()->all());
            }
        });
    }

    /** @return array{0:?int,1:?int,2:?int} */
    private function parseLocation(?string $location): array
    {
        if (! $location || ! preg_match('/^(\d+):(\d+):(\d+)$/', $location, $match)) {
            return [null,null,null];
        }
        return [(int)$match[1],(int)$match[2],(int)$match[3]];
    }

    public function syncPage(int $page, bool $force = false): void
    {
        if ($page < 1 || $page > self::TOTAL_PAGES) {
            throw new \InvalidArgumentException('Halaman Mushaf harus 1–604.');
        }
        if (! $force && QuranMushafLine::query()->where('layout_code',$this->layoutCode())->where('page_number',$page)->exists()) {
            return;
        }

        $template = (string) config('sullam.mushaf_line_page_url');
        if ($template === '') {
            throw new \RuntimeException('Template URL halaman Mushaf Line belum dikonfigurasi.');
        }

        $urls = [];
        if (str_contains($template, '%03d')) {
            $urls[] = sprintf($template, $page);
            $urls[] = sprintf(str_replace('%03d','%d',$template), $page);
        } elseif (str_contains($template, '%d')) {
            $urls[] = sprintf($template, $page);
        } elseif (str_contains($template, '{page}')) {
            $urls[] = str_replace('{page}', (string)$page, $template);
            $urls[] = str_replace('{page}', str_pad((string)$page,3,'0',STR_PAD_LEFT), $template);
        } else {
            throw new \RuntimeException('Template URL halaman Mushaf Line belum valid.');
        }

        $lastStatus = null;
        foreach (array_values(array_unique($urls)) as $url) {
            $response = Http::connectTimeout(10)->timeout(40)->retry(2, 750)->get($url);
            $lastStatus = $response->status();
            if (! $response->successful()) {
                continue;
            }
            $payload = $response->json();
            if (! is_array($payload)) {
                continue;
            }
            $this->importPage($payload, $page);
            return;
        }

        throw new \RuntimeException('Layout Mushaf halaman '.$page.' belum dapat disinkronkan. HTTP '.($lastStatus ?? 'n/a').'.');
    }

    public function pagesForJuz(int $juz): Collection
    {
        return QuranAyah::query()->where('juz_number',$juz)->whereNotNull('page_number')
            ->distinct()->orderBy('page_number')->pluck('page_number')->map(fn($page)=>(int)$page)->values();
    }

    public function linesForPage(int $page): Collection
    {
        return QuranMushafLine::query()->where('layout_code',$this->layoutCode())->where('page_number',$page)
            ->orderBy('line_number')->get();
    }

    /** @return array{expected_pages:int,complete_pages:int,complete:bool} */
    public function coverageForJuz(int $juz): array
    {
        $pages = $this->pagesForJuz($juz);
        if ($pages->isEmpty() || ! Schema::hasTable('quran_mushaf_lines')) {
            return ['expected_pages'=>$pages->count(),'complete_pages'=>0,'complete'=>false];
        }

        $slots = QuranMushafLine::query()
            ->where('layout_code',$this->layoutCode())
            ->whereIn('page_number',$pages->all())
            ->select('page_number', DB::raw('COUNT(DISTINCT line_number) as slot_count'))
            ->groupBy('page_number')
            ->get();
        $completePages = $slots->filter(fn($row): bool => (int)$row->slot_count === self::STANDARD_LINE_SLOTS)->count();

        return [
            'expected_pages'=>$pages->count(),
            'complete_pages'=>$completePages,
            'complete'=>$completePages === $pages->count(),
        ];
    }

    /** @return array<int,array<string,mixed>> */
    public function blocksForPage(int $page, int $blockSize, int $expectedJuz): array
    {
        if (! in_array($blockSize, [3,5], true)) {
            throw new \InvalidArgumentException('Blok Mushaf hanya mendukung 3 atau 5 baris.');
        }
        $all = $this->linesForPage($page)->keyBy('line_number');
        if ($all->isEmpty()) {
            return [];
        }

        $blocks = [];
        for ($start = 1; $start <= self::STANDARD_LINE_SLOTS; $start += $blockSize) {
            $end = min(self::STANDARD_LINE_SLOTS, $start + $blockSize - 1);
            if (($end - $start + 1) !== $blockSize) {
                continue;
            }
            $slots = collect(range($start,$end))->map(fn(int $line) => $all->get($line));
            $slotsComplete = $slots->filter()->count() === $blockSize;
            $ayahLines = $slots->filter(fn($line) => $line && $line->line_type === 'ayah');
            $first = $ayahLines->first();
            $last = $ayahLines->last();
            $firstAyah = $first?->first_surah_id && $first?->first_verse
                ? QuranAyah::query()->where('surah_id',$first->first_surah_id)->where('verse_number',$first->first_verse)->first()
                : null;
            $lastAyah = $last?->last_surah_id && $last?->last_verse
                ? QuranAyah::query()->where('surah_id',$last->last_surah_id)->where('verse_number',$last->last_verse)->first()
                : null;
            $juzOk = $firstAyah && $lastAyah
                && (int)$firstAyah->juz_number === $expectedJuz
                && (int)$lastAyah->juz_number === $expectedJuz;

            $blocks[] = [
                'key'=>$page.':'.$start.'-'.$end,
                'page'=>$page,
                'start_line'=>$start,
                'end_line'=>$end,
                'slot_count'=>$blockSize,
                'lines'=>$slots->map(fn($line, $index) => [
                    'line_number'=>$line?->line_number ?? ($start + (int)$index),
                    'type'=>$line?->line_type ?? 'missing',
                    'text'=>$line?->text ?: $line?->qpc_v2,
                    'verse_range'=>$line?->verse_range,
                ])->values()->all(),
                'first_word_location'=>$first?->first_word_location,
                'last_word_location'=>$last?->last_word_location,
                'first_surah_id'=>$first?->first_surah_id,
                'first_verse'=>$first?->first_verse,
                'last_surah_id'=>$last?->last_surah_id,
                'last_verse'=>$last?->last_verse,
                'ayah_line_count'=>$ayahLines->count(),
                'has_special_lines'=>$slots->contains(fn($line) => $line && $line->line_type !== 'ayah'),
                'has_missing_slots'=>! $slotsComplete,
                'available'=>(bool)($slotsComplete && $first && $last && $juzOk),
                'crosses_juz'=>(bool)($firstAyah && $lastAyah && ((int)$firstAyah->juz_number !== $expectedJuz || (int)$lastAyah->juz_number !== $expectedJuz)),
            ];
        }
        return $blocks;
    }

    public function createLinePortion(
        Student $student,
        Teacher $teacher,
        int $page,
        int $startLine,
        int $blockSize,
        ?string $scheduledFor = null,
        ?string $dueDate = null,
        ?string $notes = null,
    ): QuranJourneyPortion {
        $profile = QuranJourneyProfile::query()->where('institution_id',$student->institution_id)->where('student_id',$student->id)->firstOrFail();
        $juz = (int)$profile->current_juz_number;
        $rule = app(QuranJourneyService::class)->ruleForJuz($juz);
        if (($rule['unit'] ?? null) !== 'line' || (int)($rule['value'] ?? 0) !== $blockSize) {
            throw ValidationException::withMessages(['line_block'=>'Marhalah aktif bukan porsi '.$blockSize.' baris.']);
        }
        if (($startLine - 1) % $blockSize !== 0 || $startLine < 1 || ($startLine + $blockSize - 1) > self::STANDARD_LINE_SLOTS) {
            throw ValidationException::withMessages(['line_block'=>'Blok harus mengikuti pola tetap Mushaf: '.($blockSize === 3 ? '1–3, 4–6, 7–9, 10–12, 13–15' : '1–5, 6–10, 11–15').'.']);
        }

        $pages = $this->pagesForJuz($juz);
        if (! $pages->contains($page)) {
            throw ValidationException::withMessages(['page_number'=>'Halaman '.$page.' tidak termasuk Juz '.$juz.'.']);
        }

        $block = collect($this->blocksForPage($page,$blockSize,$juz))->firstWhere('start_line',$startLine);
        if (! $block || ! $block['available']) {
            throw ValidationException::withMessages(['line_block'=>$block && $block['crosses_juz']
                ? 'Blok ini menyentuh batas Juz. Sistem tidak menebak porsi lintas Juz; pilih blok lain atau selesaikan batas Juz bersama guru.'
                : 'Blok baris ini belum memiliki pemetaan ayat yang valid.']);
        }

        $startAyah = QuranAyah::query()->where('surah_id',$block['first_surah_id'])->where('verse_number',$block['first_verse'])->firstOrFail();
        $endAyah = QuranAyah::query()->where('surah_id',$block['last_surah_id'])->where('verse_number',$block['last_verse'])->firstOrFail();
        $marhalah = app(QuranJourneyService::class)->marhalahForJuz($juz);
        if (! $marhalah instanceof MarhalahType) {
            throw ValidationException::withMessages(['line_block'=>'Master Marhalah belum tersedia.']);
        }
        $activeYear = AcademicYear::query()->where('institution_id',$student->institution_id)->where('is_active',true)->first();
        if (! $activeYear) {
            throw ValidationException::withMessages(['line_block'=>'Tahun ajaran aktif belum tersedia untuk membuat target setoran.']);
        }

        return DB::transaction(function () use ($student,$teacher,$page,$startLine,$blockSize,$block,$startAyah,$endAyah,$marhalah,$juz,$rule,$activeYear,$scheduledFor,$dueDate,$notes): QuranJourneyPortion {
            $endLine = $startLine + $blockSize - 1;
            $portion = QuranJourneyPortion::query()->create([
                'institution_id'=>$student->institution_id,
                'student_id'=>$student->id,
                'marhalah_type_id'=>$marhalah->id,
                'assigned_by_teacher_id'=>$teacher->id,
                'journey_juz_number'=>$juz,
                'portion_unit'=>'line',
                'portion_value'=>$blockSize,
                'portion_label'=>$rule['portion'],
                'start_global_number'=>$startAyah->global_number,
                'end_global_number'=>$endAyah->global_number,
                'start_surah_id'=>$startAyah->surah_id,
                'start_verse'=>$startAyah->verse_number,
                'end_surah_id'=>$endAyah->surah_id,
                'end_verse'=>$endAyah->verse_number,
                'start_page_number'=>$page,
                'end_page_number'=>$page,
                'mushaf_layout_code'=>$this->layoutCode(),
                'start_line_number'=>$startLine,
                'end_line_number'=>$endLine,
                'start_word_location'=>$block['first_word_location'],
                'end_word_location'=>$block['last_word_location'],
                'line_block_key'=>$block['key'],
                'selection_source'=>'mushaf_line_engine',
                'teacher_confirmed'=>true,
                'status'=>'planned',
                'scheduled_for'=>$scheduledFor,
                'due_date'=>$dueDate,
                'notes'=>$notes,
            ]);

            $ayahs = QuranAyah::query()->whereBetween('global_number',[$startAyah->global_number,$endAyah->global_number])
                ->orderBy('global_number')->get()->groupBy('surah_id');
            foreach ($ayahs as $surahId => $surahAyahs) {
                MemorizationTarget::query()->create([
                    'institution_id'=>$student->institution_id,
                    'academic_year_id'=>$activeYear->id,
                    'student_id'=>$student->id,
                    'assigned_by_teacher_id'=>$teacher->id,
                    'quran_journey_portion_id'=>$portion->id,
                    'surah_id'=>(int)$surahId,
                    'start_verse'=>(int)$surahAyahs->first()->verse_number,
                    'end_verse'=>(int)$surahAyahs->last()->verse_number,
                    'marhalah_type_id'=>$marhalah->id,
                    'journey_juz_number'=>$juz,
                    'portion_confirmed'=>true,
                    'portion_note'=>'Mushaf Madinah halaman '.$page.' · baris fisik '.$startLine.'–'.$endLine.' · batas kata '.$block['first_word_location'].' → '.$block['last_word_location'].'.',
                    'mushaf_page_number'=>$page,
                    'mushaf_start_line'=>$startLine,
                    'mushaf_end_line'=>$endLine,
                    'start_word_location'=>$block['first_word_location'],
                    'end_word_location'=>$block['last_word_location'],
                    'target_type'=>'new_memorization',
                    'status'=>'active',
                    'target_date'=>$scheduledFor,
                    'due_date'=>$dueDate,
                    'notes'=>$notes,
                ]);
            }

            return $portion->fresh(['marhalah','startSurah','endSurah','targets.surah']);
        });
    }

    private function sourceName(): string
    {
        return (string) config('sullam.mushaf_line_source_name', 'Madani Mushaf line-layout mirror');
    }
}
