<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Provider;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return [
            'provider_id' => Provider::factory(),
            'name' => fake()->randomElement(['Initial Consultation', 'Therapy Session', 'Follow-up Visit']),
            'duration_minutes' => fake()->randomElement([30, 45, 60, 90]),
            'price' => fake()->randomFloat(2, 35, 250),
            'is_active' => fake()->boolean(90),
        ];
    }
}
