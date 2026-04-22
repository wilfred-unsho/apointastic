<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Provider;
use App\Models\ProviderAvailability;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProviderAvailability>
 */
class ProviderAvailabilityFactory extends Factory
{
    protected $model = ProviderAvailability::class;

    public function definition(): array
    {
        $start = fake()->randomElement(['08:00:00', '09:00:00', '10:00:00']);

        return [
            'provider_id' => Provider::factory(),
            'day_of_week' => fake()->numberBetween(0, 6),
            'start_time' => $start,
            'end_time' => $start === '10:00:00' ? '17:00:00' : '18:00:00',
            'timezone' => fake()->randomElement(['America/New_York', 'America/Chicago', 'America/Los_Angeles']),
        ];
    }
}
