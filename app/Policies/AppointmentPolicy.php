<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Appointment;
use App\Models\Provider;
use App\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('appointment.view');
    }

    public function view(User $user, Appointment $appointment): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        $provider = Provider::query()->where('user_id', $user->id)->first();

        return $appointment->customer_id === $user->id
            || $appointment->provider_id === $provider?->id;
    }

    public function approve(User $user): bool
    {
        return $user->can('appointment.approve');
    }

    public function reject(User $user): bool
    {
        return $user->can('appointment.reject');
    }
}
