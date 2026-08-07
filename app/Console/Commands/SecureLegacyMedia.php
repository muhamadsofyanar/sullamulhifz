<?php

namespace App\Console\Commands;

use App\Models\Announcement;
use App\Models\FridayDevelopmentSession;
use App\Models\MediaAsset;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class SecureLegacyMedia extends Command
{
    protected $signature = 'sullam:secure-legacy-media {--dry-run : Hanya menghitung file yang perlu dipindahkan}';

    protected $description = 'Memindahkan media privat lama dari public storage ke penyimpanan terlindungi.';

    public function handle(): int
    {
        if (! DB::getSchemaBuilder()->hasTable('media_assets')) {
            $this->warn('Tabel media_assets belum tersedia. Jalankan migration terlebih dahulu.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $migrated = 0;
        $missing = 0;
        $failed = 0;

        Student::query()
            ->whereNull('photo_media_id')
            ->whereNotNull('photo_path')
            ->orderBy('id')
            ->chunkById(100, function ($students) use ($dryRun, &$migrated, &$missing, &$failed): void {
                foreach ($students as $student) {
                    $this->process(
                        model: $student,
                        sourcePath: $student->photo_path,
                        originalName: basename((string) $student->photo_path),
                        institutionId: (int) $student->institution_id,
                        uploaderId: null,
                        destinationDirectory: 'student-photos/legacy',
                        mediaColumn: 'photo_media_id',
                        legacyPathColumn: 'photo_path',
                        purpose: 'avatar',
                        dryRun: $dryRun,
                        migrated: $migrated,
                        missing: $missing,
                        failed: $failed,
                    );
                }
            });

        Announcement::query()
            ->whereNull('attachment_media_id')
            ->whereNotNull('attachment_path')
            ->orderBy('id')
            ->chunkById(100, function ($items) use ($dryRun, &$migrated, &$missing, &$failed): void {
                foreach ($items as $item) {
                    $this->process(
                        model: $item,
                        sourcePath: $item->attachment_path,
                        originalName: $item->attachment_original_name ?: basename((string) $item->attachment_path),
                        institutionId: (int) $item->institution_id,
                        uploaderId: $item->created_by_user_id ? (int) $item->created_by_user_id : null,
                        destinationDirectory: 'announcements/legacy',
                        mediaColumn: 'attachment_media_id',
                        legacyPathColumn: 'attachment_path',
                        purpose: 'attachment',
                        dryRun: $dryRun,
                        migrated: $migrated,
                        missing: $missing,
                        failed: $failed,
                    );
                }
            });

        FridayDevelopmentSession::query()
            ->whereNull('worksheet_media_id')
            ->whereNotNull('worksheet_path')
            ->orderBy('id')
            ->chunkById(100, function ($items) use ($dryRun, &$migrated, &$missing, &$failed): void {
                foreach ($items as $item) {
                    $this->process(
                        model: $item,
                        sourcePath: $item->worksheet_path,
                        originalName: $item->worksheet_original_name ?: basename((string) $item->worksheet_path),
                        institutionId: (int) $item->institution_id,
                        uploaderId: $item->created_by_user_id ? (int) $item->created_by_user_id : null,
                        destinationDirectory: 'friday/legacy',
                        mediaColumn: 'worksheet_media_id',
                        legacyPathColumn: 'worksheet_path',
                        purpose: 'worksheet',
                        dryRun: $dryRun,
                        migrated: $migrated,
                        missing: $missing,
                        failed: $failed,
                    );
                }
            });

        $verb = $dryRun ? 'akan dipindahkan' : 'dipindahkan';
        $this->info("Media {$verb}: {$migrated}; tidak ditemukan: {$missing}; gagal: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function process(
        Model $model,
        ?string $sourcePath,
        string $originalName,
        int $institutionId,
        ?int $uploaderId,
        string $destinationDirectory,
        string $mediaColumn,
        string $legacyPathColumn,
        string $purpose,
        bool $dryRun,
        int &$migrated,
        int &$missing,
        int &$failed,
    ): void {
        if (! $sourcePath || ! Storage::disk('public')->exists($sourcePath)) {
            $missing++;
            $this->warn($model::class.' #'.$model->getKey().': file lama tidak ditemukan.');

            return;
        }

        if ($dryRun) {
            $migrated++;

            return;
        }

        $extension = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'bin');
        $fileName = Str::uuid()->toString().'.'.$extension;
        $destinationDirectory = trim($destinationDirectory, '/');
        $destinationPath = $destinationDirectory.'/'.$fileName;
        $source = Storage::disk('public')->readStream($sourcePath);

        if (! is_resource($source)) {
            $failed++;
            $this->error($model::class.' #'.$model->getKey().': file tidak dapat dibaca.');

            return;
        }

        try {
            $written = Storage::disk('local')->writeStream($destinationPath, $source);
        } finally {
            fclose($source);
        }

        if (! $written || ! Storage::disk('local')->exists($destinationPath)) {
            $failed++;
            $this->error($model::class.' #'.$model->getKey().': file tidak dapat dipindahkan.');

            return;
        }

        try {
            DB::transaction(function () use (
                $model,
                $sourcePath,
                $originalName,
                $institutionId,
                $uploaderId,
                $destinationDirectory,
                $destinationPath,
                $fileName,
                $extension,
                $mediaColumn,
                $legacyPathColumn,
                $purpose,
            ): void {
                $absolutePath = Storage::disk('local')->path($destinationPath);
                $asset = MediaAsset::create([
                    'institution_id' => $institutionId,
                    'uploaded_by_user_id' => $uploaderId,
                    'disk' => 'local',
                    'directory' => $destinationDirectory,
                    'file_name' => $fileName,
                    'original_name' => Str::limit($originalName, 255, ''),
                    'mime_type' => Storage::disk('public')->mimeType($sourcePath) ?: 'application/octet-stream',
                    'extension' => $extension,
                    'file_size' => Storage::disk('local')->size($destinationPath),
                    'checksum' => is_file($absolutePath) ? hash_file('sha256', $absolutePath) : null,
                    'visibility' => 'restricted',
                    'processing_status' => 'ready',
                    'retention_until' => now()->addDays((int) config('sullam.media_retention_days', 180)),
                ]);

                $asset->links()->create([
                    'attachable_type' => $model->getMorphClass(),
                    'attachable_id' => $model->getKey(),
                    'purpose' => $purpose,
                    'sort_order' => 0,
                ]);

                $model->forceFill([
                    $mediaColumn => $asset->id,
                    $legacyPathColumn => null,
                ])->save();
            });

            Storage::disk('public')->delete($sourcePath);
            $migrated++;
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($destinationPath);
            $failed++;
            $this->error($model::class.' #'.$model->getKey().': '.$exception->getMessage());
        }
    }
}
