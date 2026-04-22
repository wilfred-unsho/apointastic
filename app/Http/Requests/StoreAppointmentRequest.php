<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Provider;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, \Illuminate\Contracts\Validation\ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'provider_id' => ['required', 'integer', 'exists:providers,id'],
            'service_id' => ['required', 'integer', 'exists:services,id'],
            'starts_at_local' => ['required', 'date'],
            'user_timezone' => ['required', 'string', Rule::in(timezone_identifiers_list())],
            'starts_at_utc' => ['sometimes', 'date'],
            'ends_at_utc' => ['sometimes', 'date'],
            'customer_id' => ['sometimes', 'integer', 'exists:users,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $provider = Provider::query()->find((int) $this->input('provider_id'));
            $service = Service::query()->find((int) $this->input('service_id'));

            if (! $provider || ! $service) {
                return;
            }

            if ($service->provider_id !== $provider->id) {
                $validator->errors()->add('service_id', 'Selected service does not belong to the provider.');

                return;
            }

            $userTimezone = (string) $this->input('user_timezone');
            $startAtLocal = CarbonImmutable::parse((string) $this->input('starts_at_local'), $userTimezone);
            $startsAtUtc = $startAtLocal->utc();
            $endsAtUtc = $startsAtUtc->addMinutes((int) $service->duration_minutes);

            if ($startsAtUtc->lessThanOrEqualTo(CarbonImmutable::now('UTC'))) {
                $validator->errors()->add('starts_at_local', 'Appointments must be scheduled in the future.');

                return;
            }

            $providerClockStart = $startsAtUtc->setTimezone($provider->timezone);
            $providerClockEnd = $endsAtUtc->setTimezone($provider->timezone);

            $availability = $provider->availabilities()
                ->where('day_of_week', $providerClockStart->dayOfWeek)
                ->first();

            if (! $availability) {
                $validator->errors()->add('starts_at_local', 'Provider is unavailable on the selected day.');

                return;
            }

            $availabilityStart = CarbonImmutable::parse(
                $providerClockStart->toDateString().' '.$availability->start_time,
                $availability->timezone,
            );

            $availabilityEnd = CarbonImmutable::parse(
                $providerClockStart->toDateString().' '.$availability->end_time,
                $availability->timezone,
            );

            if ($providerClockStart->lessThan($availabilityStart) || $providerClockEnd->greaterThan($availabilityEnd)) {
                $validator->errors()->add('starts_at_local', 'Selected slot is outside provider availability.');

                return;
            }

            $offset = $availabilityStart->diffInMinutes($providerClockStart, false);
            if ($offset < 0 || $offset % (int) $service->duration_minutes !== 0) {
                $validator->errors()->add('starts_at_local', 'Selected slot does not align to the service duration.');

                return;
            }

            $this->merge([
                'starts_at_utc' => $startsAtUtc,
                'ends_at_utc' => $endsAtUtc,
                'customer_id' => $this->user()->id,
            ]);
        });
    }
}
