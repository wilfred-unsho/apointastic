<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Manage Availability</h2>
    </x-slot>

    <div class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <livewire:provider.availability-manager />
            <div class="mt-6 rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <h3 class="mb-3 text-sm font-semibold text-slate-800">Availability Preview</h3>
                @php
                    $providerId = \App\Models\Provider::query()->where('user_id', auth()->id())->value('id');
                @endphp
                <div id="provider-availability-preview" class="min-h-[400px]" data-provider-id="{{ $providerId }}"></div>
            </div>
        </div>
    </div>
</x-app-layout>
