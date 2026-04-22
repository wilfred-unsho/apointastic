import { Calendar } from '@fullcalendar/core';
import timeGridPlugin from '@fullcalendar/timegrid';

const fetchAvailabilityEvents = async (providerId) => {
    if (!providerId) {
        return [];
    }

    const response = await fetch(`/api/providers/${providerId}/availability-preview`, {
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
        },
        credentials: 'same-origin',
    });

    const payload = await response.json();
    return payload.data ?? [];
};

const initProviderAvailabilityCalendar = () => {
    const root = document.getElementById('provider-availability-preview');
    if (!root) {
        return;
    }

    const calendar = new Calendar(root, {
        plugins: [timeGridPlugin],
        initialView: 'timeGridWeek',
        allDaySlot: false,
        editable: false,
        selectable: false,
        events: async (fetchInfo, successCallback) => {
            const events = await fetchAvailabilityEvents(root.dataset.providerId);
            successCallback(events);
        },
    });

    calendar.render();

    window.addEventListener('availability-updated', () => {
        calendar.refetchEvents();
    });
};

document.addEventListener('livewire:navigated', initProviderAvailabilityCalendar);
document.addEventListener('DOMContentLoaded', initProviderAvailabilityCalendar);
