<?php

use App\Jobs\RecordWorkerHeartbeat;
use App\Services\OperationalHeartbeat;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('growpos:reconcile-payments')->everyMinute()->withoutOverlapping(10);
Schedule::command('growpos:backup')->dailyAt('02:00')->withoutOverlapping(120);

Schedule::call(function () {
    app(OperationalHeartbeat::class)->record('scheduler');
    RecordWorkerHeartbeat::dispatch(now()->toDateTimeString());
})->name('operational-heartbeat')->everyMinute()->withoutOverlapping();
