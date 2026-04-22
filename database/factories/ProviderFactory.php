<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Provider>
 */
class ProviderFactory extends Factory
{
    protected $model = Provider::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'business_name' => fake()->company(),
            'timezone' => fake()->randomElement(['America/New_York', 'America/Chicago', 'America/Los_Angeles']),
            'approval_status' => fake()->randomElement([
                Provider::STATUS_PENDING,
                Provider::STATUS_APPROVED,
            ]),
        ];
    }
}
