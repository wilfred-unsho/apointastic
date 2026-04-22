<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Provider;
use App\Models\User;

class ProviderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('provider.view');
    }

    public function view(User $user, Provider $provider): bool
    {
        return $user->hasRole('admin') || $provider->user_id === $user->id;
    }

    public function approve(User $user): bool
    {
        return $user->can('provider.approve');
    }

    public function reject(User $user): bool
    {
        return $user->can('provider.reject');
    }
}
