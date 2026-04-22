<?php

declare(strict_types=1);

use App\Console\Commands\CleanupPendingAppointmentsCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment('Keep building excellent scheduling experiences.');
})->purpose('Display an inspiring quote');

Schedule::command(CleanupPendingAppointmentsCommand::class)
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();
