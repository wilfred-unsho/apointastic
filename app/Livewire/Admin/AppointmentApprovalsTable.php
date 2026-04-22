<?php

declare(strict_types=1);

namespace App\Livewire\Admin;

use App\Models\Appointment;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class AppointmentApprovalsTable extends Component
{
    use WithPagination;

    public string $statusFilter = 'pending';

    public string $search = '';

    public function approve(int $appointmentId): void
    {
        $this->authorize('approve', Appointment::class);

        $appointment = Appointment::query()->findOrFail($appointmentId);
        $appointment->update(['status' => Appointment::STATUS_CONFIRMED]);

        Log::info('Appointment approved by admin.', [
            'appointment_id' => $appointment->id,
            'admin_user_id' => auth()->id(),
        ]);
    }

    public function reject(int $appointmentId): void
    {
        $this->authorize('reject', Appointment::class);

        $appointment = Appointment::query()->findOrFail($appointmentId);
        $appointment->update(['status' => Appointment::STATUS_CANCELLED]);

        Log::info('Appointment rejected by admin.', [
            'appointment_id' => $appointment->id,
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
        $this->authorize('viewAny', Appointment::class);

        $appointments = Appointment::query()
            ->with(['provider:id,business_name', 'customer:id,name,email', 'service:id,name'])
            ->when($this->statusFilter !== 'all', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner->whereHas('customer', fn ($customerQuery) => $customerQuery->where('email', 'like', '%'.$this->search.'%'))
                        ->orWhereHas('service', fn ($serviceQuery) => $serviceQuery->where('name', 'like', '%'.$this->search.'%'));
                });
            })
            ->orderBy('starts_at_utc')
            ->paginate(10);

        return view('livewire.admin.appointment-approvals-table', [
            'appointments' => $appointments,
        ]);
    }
}
