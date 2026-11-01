<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class BackupArchive
{
    /** Copy a complete local backup and verify its remote bytes before pruning anything. */
    public function replicate(string $path): void
    {
        $diskName = config('backup.offsite_disk');
        if (! $diskName) {
            if (app()->environment('production')) {
                throw new \RuntimeException('Configure GROWPOS_BACKUP_DISK for an independent private backup destination.');
            }

            return;
        }
        if (in_array($diskName, ['local', 'public'], true)) {
            throw new \RuntimeException('The offsite destination must be an independent private disk.');
        }
        $disk = Storage::disk($diskName);
        $prefix = config('backup.prefix', 'growpos-backups');
        $object = $prefix.'/'.basename($path);
        $stream = fopen($path, 'rb');
        try {
            if (! $disk->put($object, $stream, ['visibility' => 'private', 'ServerSideEncryption' => 'AES256'])) {
                throw new \RuntimeException('Offsite backup upload failed.');
            }
        } finally {
            fclose($stream);
        }
        $remote = $disk->readStream($object);
        if (! is_resource($remote)) {
            throw new \RuntimeException('Offsite backup verification failed.');
        }
        try {
            $hash = hash_init('sha256');
            hash_update_stream($hash, $remote);
            if (! hash_equals(hash_file('sha256', $path), hash_final($hash))) {
                throw new \RuntimeException('Offsite backup checksum differs.');
            }
        } finally {
            fclose($remote);
        }
        if (! $disk->put($object.'.sha256', hash_file('sha256', $path), ['visibility' => 'private', 'ServerSideEncryption' => 'AES256'])) {
            throw new \RuntimeException('Offsite checksum upload failed.');
        }
        $cutoff = now()->subDays(max(2, (int) config('backup.retention_days', 14)))->timestamp;
        foreach ($disk->files($prefix) as $old) {
            if (preg_match('/\.(sql|sqlite)$/', $old) && $old !== $object && $disk->lastModified($old) < $cutoff) {
                $disk->delete([$old, $old.'.sha256']);
            }
        }
        foreach (glob(dirname($path).'/*.{sql,sqlite}', GLOB_BRACE) as $old) {
            if ($old !== $path && filemtime($old) < $cutoff && is_file($old.'.sha256')) {
                unlink($old);
                unlink($old.'.sha256');
            }
        }
    }
}
