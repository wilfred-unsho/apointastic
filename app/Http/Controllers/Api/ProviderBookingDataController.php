<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Provider;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderBookingDataController extends Controller
{
    public function services(Provider $provider): JsonResponse
    {
        $services = Service::query()
            ->where('provider_id', $provider->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'duration_minutes', 'price']);

        return response()->json(['data' => $services]);
    }

    public function slots(Request $request, Provider $provider): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'timezone' => ['required', 'string', 'in:'.implode(',', timezone_identifiers_list())],
        ]);

        $service = Service::query()
            ->where('provider_id', $provider->id)
            ->where('id', (int) $validated['service_id'])
            ->firstOrFail();

        $windowStart = CarbonImmutable::parse((string) $validated['start'], 'UTC');
        $windowEnd = CarbonImmutable::parse((string) $validated['end'], 'UTC');
        $userTimezone = (string) $validated['timezone'];
        $duration = (int) $service->duration_minutes;

        $events = [];

        $availabilities = $provider->availabilities()->get();

        foreach ($availabilities as $availability) {
            $cursor = $windowStart->setTimezone($provider->timezone)->startOfDay();
            $cursorEnd = $windowEnd->setTimezone($provider->timezone)->endOfDay();

            while ($cursor->lessThanOrEqualTo($cursorEnd)) {
                if ($cursor->dayOfWeek !== (int) $availability->day_of_week) {
                    $cursor = $cursor->addDay();
                    continue;
                }

                $dayStart = CarbonImmutable::parse($cursor->toDateString().' '.$availability->start_time, $availability->timezone);
                $dayEnd = CarbonImmutable::parse($cursor->toDateString().' '.$availability->end_time, $availability->timezone);

                $slotStart = $dayStart;
                while ($slotStart->addMinutes($duration)->lessThanOrEqualTo($dayEnd)) {
                    $slotEnd = $slotStart->addMinutes($duration);
                    $slotStartUtc = $slotStart->utc();
                    $slotEndUtc = $slotEnd->utc();

                    if ($slotStartUtc->lessThan(CarbonImmutable::now('UTC'))) {
                        $slotStart = $slotStart->addMinutes($duration);
                        continue;
                    }

                    $overlaps = Appointment::query()
                        ->where('provider_id', $provider->id)
                        ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
                        ->where('starts_at_utc', '<', $slotEndUtc)
                        ->where('ends_at_utc', '>', $slotStartUtc)
                        ->exists();

                    if (! $overlaps) {
                        $events[] = [
                            'id' => $provider->id.'-'.$service->id.'-'.$slotStartUtc->timestamp,
                            'title' => $service->name,
                            'start' => $slotStartUtc->setTimezone($userTimezone)->toIso8601String(),
                            'end' => $slotEndUtc->setTimezone($userTimezone)->toIso8601String(),
                            'extendedProps' => [
                                'provider_id' => $provider->id,
                                'service_id' => $service->id,
                            ],
                        ];
                    }

                    $slotStart = $slotStart->addMinutes($duration);
                }

                $cursor = $cursor->addDay();
            }
        }

        return response()->json(['data' => $events]);
    }

    public function weeklyAvailabilityPreview(Provider $provider): JsonResponse
    {
        $events = $provider->availabilities()
            ->get()
            ->map(fn ($availability): array => [
                'title' => 'Available',
                'daysOfWeek' => [(int) $availability->day_of_week],
                'startTime' => substr((string) $availability->start_time, 0, 5),
                'endTime' => substr((string) $availability->end_time, 0, 5),
                'display' => 'background',
                'color' => '#bbf7d0',
            ])
            ->values();

        return response()->json(['data' => $events]);
    }
}
