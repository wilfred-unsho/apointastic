<?php

declare(strict_types=1);

use App\Models\Appointment;
use App\Models\Provider;
use App\Models\ProviderAvailability;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('hydrates core model relationships', function (): void {
    $provider = Provider::factory()->create();
    $service = Service::factory()->create(['provider_id' => $provider->id]);
    $availability = ProviderAvailability::factory()->create(['provider_id' => $provider->id]);
    $customer = User::factory()->create();

    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'provider_id' => $provider->id,
        'service_id' => $service->id,
        'starts_at_utc' => CarbonImmutable::parse('2026-05-01 15:00:00', 'UTC'),
        'ends_at_utc' => CarbonImmutable::parse('2026-05-01 16:00:00', 'UTC'),
        'user_timezone' => 'America/New_York',
        'status' => Appointment::STATUS_PENDING,
    ]);

    expect($provider->services)->toHaveCount(1)
        ->and($provider->availabilities)->toHaveCount(1)
        ->and($provider->appointments)->toHaveCount(1)
        ->and($service->provider->is($provider))->toBeTrue()
        ->and($availability->provider->is($provider))->toBeTrue()
        ->and($appointment->provider->is($provider))->toBeTrue()
        ->and($appointment->service->is($service))->toBeTrue()
        ->and($appointment->customer->is($customer))->toBeTrue();
});

it('converts local appointment times to utc in mutators and back in accessors', function (): void {
    $provider = Provider::factory()->create(['timezone' => 'America/Chicago']);
    $service = Service::factory()->create(['provider_id' => $provider->id]);
    $customer = User::factory()->create();

    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'provider_id' => $provider->id,
        'service_id' => $service->id,
        'user_timezone' => 'America/New_York',
        'starts_at_local' => '2026-05-01 10:00:00',
        'ends_at_local' => '2026-05-01 11:00:00',
        'status' => Appointment::STATUS_CONFIRMED,
    ]);

    expect($appointment->starts_at_utc->format('Y-m-d H:i:s'))->toBe('2026-05-01 14:00:00')
        ->and($appointment->ends_at_utc->format('Y-m-d H:i:s'))->toBe('2026-05-01 15:00:00')
        ->and($appointment->starts_at_local?->format('Y-m-d H:i:s'))->toBe('2026-05-01 10:00:00')
        ->and($appointment->ends_at_local?->format('Y-m-d H:i:s'))->toBe('2026-05-01 11:00:00');
});

it('exposes allowed provider and appointment statuses', function (): void {
    expect(Provider::STATUS_PENDING)->toBe('pending')
        ->and(Provider::STATUS_APPROVED)->toBe('approved')
        ->and(Provider::STATUS_REJECTED)->toBe('rejected')
        ->and(Appointment::statuses())->toBe([
            Appointment::STATUS_PENDING,
            Appointment::STATUS_CONFIRMED,
            Appointment::STATUS_COMPLETED,
            Appointment::STATUS_CANCELLED,
        ]);
});
