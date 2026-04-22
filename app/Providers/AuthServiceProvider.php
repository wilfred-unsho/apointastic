<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Appointment;
use App\Models\Provider;
use App\Policies\AppointmentPolicy;
use App\Policies\ProviderPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Provider::class => ProviderPolicy::class,
        Appointment::class => AppointmentPolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
