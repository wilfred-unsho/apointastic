<?php

declare(strict_types=1);

namespace App\Livewire\Booking;

use App\Models\Provider;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class BookingCalendar extends Component
{
    public ?int $providerId = null;

    public ?int $serviceId = null;

    public string $timezone = 'UTC';

    public function mount(): void
    {
        $this->timezone = request()->user()?->timezone ?? 'UTC';
    }

    #[Computed]
    public function providers()
    {
        return Provider::query()
            ->where('approval_status', Provider::STATUS_APPROVED)
            ->orderBy('business_name')
            ->get(['id', 'business_name', 'timezone']);
    }

    #[Computed]
    public function services()
    {
        if (! $this->providerId) {
            return collect();
        }

        return Service::query()
            ->where('provider_id', $this->providerId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'duration_minutes', 'price']);
    }

    public function updatedProviderId(): void
    {
        $this->serviceId = null;
        $this->dispatch('provider-changed', providerId: $this->providerId);
    }

    public function updatedServiceId(): void
    {
        $this->dispatch('service-changed', serviceId: $this->serviceId);
    }

    public function updatedTimezone(): void
    {
        $this->dispatch('timezone-changed', timezone: $this->timezone);
    }

    public function render(): View
    {
        return view('livewire.booking.booking-calendar');
    }
}
