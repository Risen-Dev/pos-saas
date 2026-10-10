<?php

namespace App\Console\Commands;

use App\Services\OperationalHeartbeat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'growpos:backup';

    protected $description = 'Create a private, consistent database backup without printing credentials';

    public function handle(): int
    {
        $connection = DB::connection();
        $config = $connection->getConfig();
        $directory = storage_path('app/private/backups');
        if (! is_dir($directory)) {
            mkdir($directory, 0700, true);
        }
        $base = $directory.'/'.now()->format('Ymd-His').'-'.Str::uuid();
        $credentials = null;
        $path = $base.($config['driver'] === 'sqlite' ? '.sqlite' : '.sql');
        try {
            if ($config['driver'] === 'sqlite') {
                $connection->getPdo()->exec('VACUUM INTO '.$connection->getPdo()->quote($path));
            } elseif ($config['driver'] === 'mysql') {
                $credentials = tempnam($directory, '.mysql-');
                chmod($credentials, 0600);
                $escape = fn ($value) => '"'.str_replace(['\\', '"', "\n", "\r"], ['\\\\', '\\"', '\\n', '\\r'], (string) $value).'"';
                $options = ['user' => $config['username'], 'password' => $config['password'], 'host' => $config['host'], 'port' => $config['port']];
                if (! empty($config['unix_socket'])) {
                    $options['socket'] = $config['unix_socket'];
                }
                $content = "[client]\n";
                foreach ($options as $key => $value) {
                    $content .= $key.'='.$escape($value)."\n";
                }
                file_put_contents($credentials, $content);
                $process = new Process(['mysqldump', '--defaults-extra-file='.$credentials, '--single-transaction', '--skip-lock-tables', '--no-tablespaces', '--result-file='.$path, $config['database']]);
                $process->setTimeout(600);
                $process->run();
                if (! $process->isSuccessful()) {
                    throw new \RuntimeException('mysqldump gagal. Periksa koneksi dan hak akses backup pada server.');
                }
            } else {
                throw new \RuntimeException('Driver backup belum didukung.');
            }
            chmod($path, 0600);
            file_put_contents($path.'.sha256', hash_file('sha256', $path));
            chmod($path.'.sha256', 0600);
            if (Schema::hasTable('operational_heartbeats')) {
                app(OperationalHeartbeat::class)->record('backup');
            }
            $this->info('Backup tersimpan: '.$path);
            $this->line('Checksum SHA-256 tersimpan. Salin ke penyimpanan privat terpisah dan lakukan uji restore.');

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            if (is_file($path)) {
                unlink($path);
            }
            $this->error('Backup tidak selesai. Database asli tidak diubah.');

            return self::FAILURE;
        } finally {
            if ($credentials && is_file($credentials)) {
                unlink($credentials);
            }
        }
    }
}
