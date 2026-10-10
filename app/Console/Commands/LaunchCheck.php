<?php

namespace App\Console\Commands;

use App\Services\OperationalHeartbeat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LaunchCheck extends Command
{
    protected $signature = 'growpos:launch-check';

    protected $description = 'Check production prerequisites without printing credentials or modifying data';

    public function handle(): int
    {
        $checks = [
            'APP_ENV production' => app()->environment('production'),
            'APP_DEBUG disabled' => ! config('app.debug'),
            'APP_URL HTTPS' => str_starts_with((string) config('app.url'), 'https://'),
            'Session cookie secure' => (bool) config('session.secure'),
            'Session cookie HTTP only' => (bool) config('session.http_only'),
            'Application encryption key configured' => filled(config('app.key')),
            'Queue has persistent worker driver' => in_array(config('queue.default'), ['database', 'redis', 'sqs'], true),
            'Mail uses delivery transport' => ! in_array(config('mail.default'), ['log', 'array'], true),
            'Midtrans live mode enabled' => (bool) config('services.midtrans.is_production'),
            'Midtrans server key configured' => filled(config('services.midtrans.server_key')),
            'SMTP host configured' => config('mail.default') !== 'smtp' || filled(config('mail.mailers.smtp.host')),
            'Support contact configured' => filled(config('launch.support_email')),
        ];
        try {
            DB::connection()->getPdo();
            $checks['Database reachable'] = true;
            $heartbeat = app(OperationalHeartbeat::class);
            $checks['Scheduler heartbeat within 3 minutes'] = $heartbeat->fresh('scheduler', 3);
            $checks['Worker handled a recent job within 3 minutes'] = $heartbeat->fresh('worker', 3);
            $checks['Backup completed within 26 hours'] = $heartbeat->fresh('backup', 26 * 60);
            $checks['Application build manifest present'] = is_file(public_path('build/manifest.json'));
            $checks['Email stylesheet built'] = is_file(public_path('build/mail.css'));
            foreach (['cash_entries', 'order_returns', 'failed_jobs'] as $table) {
                $checks['Table '.$table] = Schema::hasTable($table);
            }
            $checks['No failed queue jobs'] = Schema::hasTable('failed_jobs') && DB::table('failed_jobs')->count() === 0;
        } catch (\Throwable) {
            $checks['Database reachable'] = false;
        }
        foreach ($checks as $label => $passed) {
            $this->line(($passed ? 'PASS ' : 'FAIL ').$label);
        }
        $this->line('This check does not certify backup restore, payment provider, hardware, uptime or legal readiness.');

        return in_array(false, $checks, true) ? self::FAILURE : self::SUCCESS;
    }
}
