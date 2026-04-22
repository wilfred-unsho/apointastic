<?php

declare(strict_types=1);

namespace App\Services\Bookings;

use App\Jobs\Appointments\SendAppointmentConfirmationJob;
use App\Jobs\Appointments\SendAppointmentReminderJob;
use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentBookingService
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Appointment
    {
        return DB::transaction(function () use ($data): Appointment {
            $startsAtUtc = $this->asImmutable(Arr::get($data, 'starts_at_utc'));
            $endsAtUtc = $this->asImmutable(Arr::get($data, 'ends_at_utc'));

            if ($startsAtUtc->greaterThanOrEqualTo($endsAtUtc)) {
                throw ValidationException::withMessages([
                    'starts_at_local' => 'Appointment start time must be before end time.',
                ]);
            }

            $overlapsExisting = Appointment::query()
                ->where('provider_id', Arr::get($data, 'provider_id'))
                ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
                ->where('starts_at_utc', '<', $endsAtUtc)
                ->where('ends_at_utc', '>', $startsAtUtc)
                ->lockForUpdate()
                ->exists();

            if ($overlapsExisting) {
                throw ValidationException::withMessages([
                    'starts_at_local' => 'This slot is no longer available. Please choose a different time.',
                ]);
            }

            /** @var Appointment $appointment */
            $appointment = Appointment::query()->create([
                'customer_id' => Arr::get($data, 'customer_id'),
                'provider_id' => Arr::get($data, 'provider_id'),
                'service_id' => Arr::get($data, 'service_id'),
                'starts_at_utc' => $startsAtUtc,
                'ends_at_utc' => $endsAtUtc,
                'user_timezone' => Arr::get($data, 'user_timezone', 'UTC'),
                'status' => Appointment::STATUS_PENDING,
            ]);

            SendAppointmentConfirmationJob::dispatch($appointment->id);
            SendAppointmentReminderJob::dispatch($appointment->id, 'reminder_24h')
                ->delay($startsAtUtc->subDay());
            SendAppointmentReminderJob::dispatch($appointment->id, 'reminder_1h')
                ->delay($startsAtUtc->subHour());

            return $appointment;
        });
    }

    private function asImmutable(CarbonImmutable|string $value): CarbonImmutable
    {
        if ($value instanceof CarbonImmutable) {
            return $value;
        }

        return CarbonImmutable::parse($value, 'UTC');
    }
}
