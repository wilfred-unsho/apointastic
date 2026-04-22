<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Provider;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class ProviderApprovalsTable extends Component
{
    use WithPagination;

    public string $statusFilter = 'pending';

    public string $search = '';

    public function approve(int $providerId): void
    {
        $this->authorize('approve', Provider::class);

        $provider = Provider::query()->findOrFail($providerId);
        $provider->update(['approval_status' => Provider::STATUS_APPROVED]);

        Log::info('Provider approved by admin.', [
            'provider_id' => $provider->id,
            'admin_user_id' => auth()->id(),
        ]);
    }

    public function reject(int $providerId): void
    {
        $this->authorize('reject', Provider::class);

        $provider = Provider::query()->findOrFail($providerId);
        $provider->update(['approval_status' => Provider::STATUS_REJECTED]);

        Log::info('Provider rejected by admin.', [
            'provider_id' => $provider->id,
            'admin_user_id' => auth()->id(),
        ]);
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $this->authorize('viewAny', Provider::class);

        $providers = Provider::query()
            ->with('user:id,name,email')
            ->when($this->statusFilter !== 'all', fn ($query) => $query->where('approval_status', $this->statusFilter))
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner->where('business_name', 'like', '%'.$this->search.'%')
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', '%'.$this->search.'%'));
                });
            })
            ->orderBy('created_at')
            ->paginate(10);

        return view('livewire.admin.provider-approvals-table', [
            'providers' => $providers,
        ]);
    }
}
