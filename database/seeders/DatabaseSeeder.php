<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Provider;
use App\Models\ProviderAvailability;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::factory()->count(8)->create();

        Provider::factory()
            ->count(4)
            ->create()
            ->each(function (Provider $provider) use ($customers): void {
                ProviderAvailability::factory()->count(5)->create([
                    'provider_id' => $provider->id,
                    'timezone' => $provider->timezone,
                ]);

                $services = Service::factory()->count(3)->create([
                    'provider_id' => $provider->id,
                ]);

                foreach ($services as $service) {
                    $customer = $customers->random();
                    $startsAtUtc = CarbonImmutable::now('UTC')->addDays(random_int(1, 14))->setHour(15)->setMinute(0);

                    Appointment::factory()->create([
                        'customer_id' => $customer->id,
                        'provider_id' => $provider->id,
                        'service_id' => $service->id,
                        'starts_at_utc' => $startsAtUtc,
                        'ends_at_utc' => $startsAtUtc->addMinutes((int) $service->duration_minutes),
                        'user_timezone' => $customer->timezone ?? 'UTC',
                        'status' => Appointment::STATUS_CONFIRMED,
                    ]);
                }
            });
    }
}
