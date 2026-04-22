<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class CleanupPendingAppointmentsCommand extends Command
{
    protected $signature = 'scheduler:cleanup-pending';

    protected $description = 'Cancel pending appointments that were not confirmed within 15 minutes.';

    public function handle(): int
    {
        $cutoff = CarbonImmutable::now('UTC')->subMinutes(15);

        $affected = Appointment::query()
            ->where('status', Appointment::STATUS_PENDING)
            ->where('created_at', '<=', $cutoff)
            ->update(['status' => Appointment::STATUS_CANCELLED]);

        $this->info("Cancelled {$affected} pending appointments.");

        return self::SUCCESS;
    }
}
