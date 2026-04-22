<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Provider;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $startsAtUtc = CarbonImmutable::now('UTC')->addDays(fake()->numberBetween(1, 14))->setHour(14)->setMinute(0);

        return [
            'customer_id' => User::factory(),
            'provider_id' => Provider::factory(),
            'service_id' => Service::factory(),
            'starts_at_utc' => $startsAtUtc,
            'ends_at_utc' => $startsAtUtc->addMinutes(60),
            'user_timezone' => fake()->randomElement(['America/New_York', 'America/Chicago', 'America/Los_Angeles']),
            'status' => fake()->randomElement(Appointment::statuses()),
        ];
    }
}
