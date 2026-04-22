<?php

declare(strict_types=1);

namespace App\Jobs\Appointments;

use App\Models\Appointment;
use App\Notifications\AppointmentReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAppointmentConfirmationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 2;

    public function backoff(): array
    {
        return [10, 60, 120];
    }

    public function __construct(public readonly int $appointmentId)
    {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $appointment = Appointment::query()->with(['customer', 'provider', 'service'])->find($this->appointmentId);

        if (! $appointment || ! $appointment->customer) {
            return;
        }

        $appointment->customer->notify(new AppointmentReminderNotification(
            appointmentId: $appointment->id,
            startsAtUtc: $appointment->starts_at_utc->toDateTimeString(),
            providerName: $appointment->provider->business_name,
            serviceName: $appointment->service->name,
            type: 'confirmation',
        ));
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('Failed to send appointment confirmation notification.', [
            'appointment_id' => $this->appointmentId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
