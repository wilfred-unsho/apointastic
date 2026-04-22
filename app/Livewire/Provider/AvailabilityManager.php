<?php

declare(strict_types=1);

namespace App\Livewire\Provider;

use App\Models\Provider;
use App\Models\ProviderAvailability;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AvailabilityManager extends Component
{
    public Provider $provider;

    /** @var array<int, array{enabled: bool, start_time: string, end_time: string}> */
    public array $weeklyAvailability = [];

    public string $timezone = 'UTC';

    public bool $saved = false;

    public function mount(): void
    {
        $this->provider = Provider::query()->where('user_id', Auth::id())->firstOrFail();
        $this->timezone = $this->provider->timezone;

        $defaults = [];
        for ($day = 0; $day <= 6; $day++) {
            $defaults[$day] = [
                'enabled' => false,
                'start_time' => '09:00',
                'end_time' => '17:00',
            ];
        }

        $stored = ProviderAvailability::query()
            ->where('provider_id', $this->provider->id)
            ->get();

        foreach ($stored as $availability) {
            $defaults[$availability->day_of_week] = [
                'enabled' => true,
                'start_time' => substr((string) $availability->start_time, 0, 5),
                'end_time' => substr((string) $availability->end_time, 0, 5),
            ];
        }

        $this->weeklyAvailability = $defaults;
    }

    public function save(): void
    {
        $this->validate([
            'timezone' => ['required', 'string', 'in:'.implode(',', timezone_identifiers_list())],
            'weeklyAvailability' => ['required', 'array', 'size:7'],
            'weeklyAvailability.*.enabled' => ['required', 'boolean'],
            'weeklyAvailability.*.start_time' => ['required', 'date_format:H:i'],
            'weeklyAvailability.*.end_time' => ['required', 'date_format:H:i'],
        ]);

        foreach ($this->weeklyAvailability as $day => $entry) {
            if ($entry['enabled'] && $entry['start_time'] >= $entry['end_time']) {
                $this->addError("weeklyAvailability.$day.end_time", 'End time must be after start time.');

                return;
            }
        }

        $this->provider->update(['timezone' => $this->timezone]);

        ProviderAvailability::query()->where('provider_id', $this->provider->id)->delete();

        foreach ($this->weeklyAvailability as $day => $entry) {
            if (! $entry['enabled']) {
                continue;
            }

            ProviderAvailability::query()->create([
                'provider_id' => $this->provider->id,
                'day_of_week' => $day,
                'start_time' => $entry['start_time'].':00',
                'end_time' => $entry['end_time'].':00',
                'timezone' => $this->timezone,
            ]);
        }

        $this->saved = true;
        $this->dispatch('availability-updated');
    }

    public function render(): View
    {
        return view('livewire.provider.availability-manager');
    }
}
