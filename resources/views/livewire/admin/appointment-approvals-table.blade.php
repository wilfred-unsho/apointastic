<div class="space-y-4">
    <div class="grid gap-3 md:grid-cols-3">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by customer/service" class="rounded-md border-slate-300 text-sm shadow-sm" />
        <select wire:model.live="statusFilter" class="rounded-md border-slate-300 text-sm shadow-sm">
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="cancelled">Cancelled</option>
            <option value="all">All</option>
        </select>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Customer</th>
                    <th class="px-4 py-3 text-left font-semibold">Provider</th>
                    <th class="px-4 py-3 text-left font-semibold">Service</th>
                    <th class="px-4 py-3 text-left font-semibold">Start (UTC)</th>
                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                    <th class="px-4 py-3 text-right font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($appointments as $appointment)
                    <tr>
                        <td class="px-4 py-3">{{ $appointment->customer?->email }}</td>
                        <td class="px-4 py-3">{{ $appointment->provider?->business_name }}</td>
                        <td class="px-4 py-3">{{ $appointment->service?->name }}</td>
                        <td class="px-4 py-3">{{ $appointment->starts_at_utc?->toDateTimeString() }}</td>
                        <td class="px-4 py-3 capitalize">{{ $appointment->status }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <button wire:click="approve({{ $appointment->id }})" class="rounded bg-emerald-600 px-3 py-1 text-xs font-semibold text-white">Approve</button>
                            <button wire:click="reject({{ $appointment->id }})" class="rounded bg-rose-600 px-3 py-1 text-xs font-semibold text-white">Reject</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-slate-500">No appointments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $appointments->links() }}
    </div>
</div>
