import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const resolveTimezone = (fallback) => {
    try {
        return Intl.DateTimeFormat().resolvedOptions().timeZone || fallback;
    } catch {
        return fallback;
    }
};

const createEventSource = (root, emptyState, errorState) => ({
    events: async (fetchInfo, successCallback, failureCallback) => {
        const providerId = root.dataset.providerId;
        const serviceId = root.dataset.serviceId;
        const timezone = resolveTimezone(root.dataset.timezone || 'UTC');

        if (!providerId || !serviceId) {
            emptyState.classList.remove('hidden');
            successCallback([]);
            return;
        }

        emptyState.classList.add('hidden');
        errorState.classList.add('hidden');

        try {
            const params = new URLSearchParams({
                start: fetchInfo.startStr,
                end: fetchInfo.endStr,
                timezone,
                service_id: serviceId,
            });

            const response = await fetch(`/api/providers/${providerId}/slots?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                credentials: 'same-origin',
            });

            const payload = await response.json();
            if (!response.ok) {
                throw new Error(payload.message || 'Unable to load available slots.');
            }

            if (!Array.isArray(payload.data) || payload.data.length === 0) {
                emptyState.classList.remove('hidden');
            }

            successCallback(payload.data ?? []);
        } catch (error) {
            errorState.textContent = error.message;
            errorState.classList.remove('hidden');
            failureCallback(error);
        }
    },
});

const initBookingCalendar = () => {
    const root = document.getElementById('customer-booking-calendar');
    const emptyState = document.getElementById('booking-empty-state');
    const errorState = document.getElementById('booking-error-state');

    if (!root || !emptyState || !errorState) {
        return;
    }

    const calendar = new Calendar(root, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        initialView: 'timeGridWeek',
        allDaySlot: false,
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        nowIndicator: true,
        selectable: true,
        eventSources: [createEventSource(root, emptyState, errorState)],
    });

    calendar.render();

    window.addEventListener('provider-changed', (event) => {
        root.dataset.providerId = event.detail.providerId || '';
        calendar.refetchEvents();
    });

    window.addEventListener('service-changed', (event) => {
        root.dataset.serviceId = event.detail.serviceId || '';
        calendar.refetchEvents();
    });

    window.addEventListener('timezone-changed', (event) => {
        root.dataset.timezone = event.detail.timezone || 'UTC';
        calendar.refetchEvents();
    });
};

document.addEventListener('livewire:navigated', initBookingCalendar);
document.addEventListener('DOMContentLoaded', initBookingCalendar);
