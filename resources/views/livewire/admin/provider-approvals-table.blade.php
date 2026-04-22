<div class="space-y-4">
    <div class="grid gap-3 md:grid-cols-3">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by business/email" class="rounded-md border-slate-300 text-sm shadow-sm" />
        <select wire:model.live="statusFilter" class="rounded-md border-slate-300 text-sm shadow-sm">
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="all">All</option>
        </select>
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Business</th>
                    <th class="px-4 py-3 text-left font-semibold">Owner</th>
                    <th class="px-4 py-3 text-left font-semibold">Timezone</th>
                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                    <th class="px-4 py-3 text-right font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($providers as $provider)
                    <tr>
                        <td class="px-4 py-3">{{ $provider->business_name }}</td>
                        <td class="px-4 py-3">{{ $provider->user?->email }}</td>
                        <td class="px-4 py-3">{{ $provider->timezone }}</td>
                        <td class="px-4 py-3 capitalize">{{ $provider->approval_status }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <button wire:click="approve({{ $provider->id }})" class="rounded bg-emerald-600 px-3 py-1 text-xs font-semibold text-white">Approve</button>
                            <button wire:click="reject({{ $provider->id }})" class="rounded bg-rose-600 px-3 py-1 text-xs font-semibold text-white">Reject</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-500">No providers found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $providers->links() }}
    </div>
</div>
