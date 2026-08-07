<?php

namespace App\Services;

use App\Models\MediaAsset;
use App\Models\MediaLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class MediaStorageService
{
    public function store(
        UploadedFile $file,
        User $uploader,
        string $directory,
        string $visibility = 'private',
        ?int $retentionDays = null,
    ): MediaAsset {
        $visibility = in_array($visibility, ['private', 'restricted', 'public'], true) ? $visibility : 'private';
        $disk = $visibility === 'public' ? 'public' : 'local';
        $directory = trim(preg_replace('/[^a-zA-Z0-9_\/-]+/', '-', $directory) ?: 'uploads', '/');
        $extension = strtolower($file->guessExtension() ?: $file->getClientOriginalExtension() ?: 'bin');
        $fileName = Str::uuid()->toString().'.'.$extension;
        $path = $file->storeAs($directory, $fileName, $disk);

        if (! $path) {
            throw new RuntimeException('File gagal disimpan.');
        }

        try {
            return DB::transaction(function () use ($file, $uploader, $directory, $fileName, $extension, $disk, $visibility, $retentionDays): MediaAsset {
                $absolutePath = Storage::disk($disk)->path($directory.'/'.$fileName);

                return MediaAsset::create([
                    'institution_id' => $uploader->institution_id,
                    'uploaded_by_user_id' => $uploader->id,
                    'disk' => $disk,
                    'directory' => $directory,
                    'file_name' => $fileName,
                    'original_name' => Str::limit($file->getClientOriginalName(), 255, ''),
                    'mime_type' => $file->getMimeType(),
                    'extension' => $extension,
                    'file_size' => $file->getSize() ?: 0,
                    'checksum' => is_file($absolutePath) ? hash_file('sha256', $absolutePath) : null,
                    'visibility' => $visibility,
                    'processing_status' => 'ready',
                    'retention_until' => $retentionDays ? now()->addDays($retentionDays) : null,
                ]);
            });
        } catch (\Throwable $exception) {
            Storage::disk($disk)->delete($directory.'/'.$fileName);
            throw $exception;
        }
    }

    public function link(MediaAsset $asset, Model $attachable, string $purpose = 'attachment', int $sortOrder = 0): MediaLink
    {
        return MediaLink::updateOrCreate([
            'media_asset_id' => $asset->id,
            'attachable_type' => $attachable->getMorphClass(),
            'attachable_id' => $attachable->getKey(),
            'purpose' => $purpose,
        ], ['sort_order' => $sortOrder]);
    }

    public function delete(MediaAsset $asset): void
    {
        DB::transaction(function () use ($asset): void {
            Storage::disk($asset->disk)->delete($asset->storagePath());
            $asset->links()->delete();
            $asset->delete();
        });
    }
}
