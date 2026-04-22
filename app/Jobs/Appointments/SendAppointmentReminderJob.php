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

class SendAppointmentReminderJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $maxExceptions = 2;

    public function backoff(): array
    {
        return [60, 300, 600];
    }

    public function __construct(
        public readonly int $appointmentId,
        public readonly string $type,
    ) {
        $this->onQueue('notifications');
    }

    public function handle(): void
    {
        $appointment = Appointment::query()->with(['customer', 'provider', 'service'])->find($this->appointmentId);

        if (! $appointment || ! $appointment->customer || $appointment->status === Appointment::STATUS_CANCELLED) {
            return;
        }

        $appointment->customer->notify(new AppointmentReminderNotification(
            appointmentId: $appointment->id,
            startsAtUtc: $appointment->starts_at_utc->toDateTimeString(),
            providerName: $appointment->provider->business_name,
            serviceName: $appointment->service->name,
            type: $this->type,
        ));
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('Failed to send appointment reminder notification.', [
            'appointment_id' => $this->appointmentId,
            'type' => $this->type,
            'error' => $exception?->getMessage(),
        ]);
    }
}
