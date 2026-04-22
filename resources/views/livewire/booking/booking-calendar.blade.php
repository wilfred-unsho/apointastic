<div class="mx-auto max-w-6xl space-y-6" data-booking-calendar-wrapper>
    <div class="grid gap-4 rounded-lg border border-slate-200 bg-white p-6 shadow-sm md:grid-cols-3">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Provider</label>
            <select wire:model.live="providerId" class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Select a provider</option>
                @foreach ($this->providers as $provider)
                    <option value="{{ $provider->id }}">{{ $provider->business_name }} ({{ $provider->timezone }})</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Service</label>
            <select wire:model.live="serviceId" @disabled(!$providerId) class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-slate-100">
                <option value="">Select a service</option>
                @foreach ($this->services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }} ({{ $service->duration_minutes }} min)</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Your timezone</label>
            <input type="text" wire:model.live="timezone" class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
            @error('timezone')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div wire:loading.flex class="items-center gap-2 rounded-md border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" class="stroke-slate-400" stroke-width="4" /><path d="M22 12a10 10 0 0 0-10-10" class="stroke-indigo-600" stroke-width="4" /></svg>
        Loading availability...
    </div>

    <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div id="customer-booking-calendar" class="min-h-[520px]" data-provider-id="{{ $providerId }}" data-service-id="{{ $serviceId }}" data-timezone="{{ $timezone }}"></div>
        <p id="booking-empty-state" class="mt-3 hidden rounded-md bg-slate-50 px-3 py-2 text-sm text-slate-500">No slots available for this date.</p>
        <p id="booking-error-state" class="mt-3 hidden rounded-md bg-rose-50 px-3 py-2 text-sm text-rose-700"></p>
    </div>

    <meta name="csrf-token" content="{{ csrf_token() }}" />
</div>
