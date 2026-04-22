<div class="mx-auto max-w-5xl space-y-6">
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Weekly Availability</h2>
        <p class="mt-1 text-sm text-slate-500">Set the times customers can book. All values are stored in UTC after conversion.</p>

        <div class="mt-4">
            <label for="timezone" class="mb-1 block text-sm font-medium text-slate-700">Provider timezone</label>
            <select id="timezone" wire:model.live="timezone" class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach (timezone_identifiers_list() as $tz)
                    <option value="{{ $tz }}">{{ $tz }}</option>
                @endforeach
            </select>
            @error('timezone')
                <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-slate-700">Day</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-700">Enabled</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-700">Start</th>
                    <th class="px-4 py-3 text-left font-semibold text-slate-700">End</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayIndex => $dayLabel)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $dayLabel }}</td>
                        <td class="px-4 py-3">
                            <input type="checkbox" wire:model.live="weeklyAvailability.{{ $dayIndex }}.enabled" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
                        </td>
                        <td class="px-4 py-3">
                            <input type="time" wire:model.live="weeklyAvailability.{{ $dayIndex }}.start_time" class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                        </td>
                        <td class="px-4 py-3">
                            <input type="time" wire:model.live="weeklyAvailability.{{ $dayIndex }}.end_time" class="w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            @error('weeklyAvailability.'.$dayIndex.'.end_time')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex items-center gap-3">
        <button wire:click="save" wire:loading.attr="disabled" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 disabled:opacity-50">
            <span wire:loading.remove wire:target="save">Save availability</span>
            <span wire:loading wire:target="save">Saving...</span>
        </button>

        @if ($saved)
            <p class="text-sm text-emerald-600">Availability saved.</p>
        @endif
    </div>
</div>
