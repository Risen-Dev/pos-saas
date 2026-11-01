<?php

namespace Tests\Feature\Pos;

use App\Services\BackupArchive;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BackupArchiveTest extends PosTestCase
{
    public function test_backup_bytes_are_verified_before_old_local_copies_are_pruned(): void
    {
        Storage::fake('archive');
        config(['backup.offsite_disk' => 'archive', 'backup.retention_days' => 14]);
        $directory = storage_path('framework/testing/backups-'.Str::uuid());
        File::ensureDirectoryExists($directory);
        try {
            $path = $directory.'/new.sql';
            $old = $directory.'/old.sql';
            file_put_contents($path, 'Synthetic database backup');
            file_put_contents($old, 'Old synthetic backup');
            file_put_contents($old.'.sha256', hash_file('sha256', $old));
            touch($old, now()->subDays(30)->timestamp);
            app(BackupArchive::class)->replicate($path);
            Storage::disk('archive')->assertExists('growpos-backups/new.sql');
            $this->assertSame(hash_file('sha256', $path), Storage::disk('archive')->get('growpos-backups/new.sql.sha256'));
            $this->assertFileExists($path);
            $this->assertFileDoesNotExist($old);
        } finally {
            File::deleteDirectory($directory);
        }
    }

    public function test_public_or_same_local_backup_destination_is_rejected(): void
    {
        config(['backup.offsite_disk' => 'public']);
        $this->expectException(\RuntimeException::class);
        app(BackupArchive::class)->replicate('not-read');
    }
}
