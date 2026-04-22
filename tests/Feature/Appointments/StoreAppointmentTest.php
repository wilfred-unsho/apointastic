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

function createBookableProvider(string $timezone = 'America/New_York'): array {
    $provider = Provider::factory()->create(['timezone' => $timezone]);

    ProviderAvailability::factory()->create([
        'provider_id' => $provider->id,
        'day_of_week' => 1,
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'timezone' => $timezone,
    ]);

    $service = Service::factory()->create([
        'provider_id' => $provider->id,
        'duration_minutes' => 60,
    ]);

    return [$provider, $service];
}

it('creates a valid pending booking', function (): void {
    $customer = User::factory()->create();
    [$provider, $service] = createBookableProvider();

    $response = $this->actingAs($customer)->postJson(route('appointments.store'), [
        'provider_id' => $provider->id,
        'service_id' => $service->id,
        'starts_at_local' => '2030-04-01 10:00:00',
        'user_timezone' => 'America/New_York',
    ]);

    $response->assertCreated()
        ->assertJsonPath('status', Appointment::STATUS_PENDING);

    $this->assertDatabaseHas('appointments', [
        'provider_id' => $provider->id,
        'service_id' => $service->id,
        'customer_id' => $customer->id,
        'status' => Appointment::STATUS_PENDING,
    ]);
});

it('handles timezone edge case booking and stores utc correctly', function (): void {
    $customer = User::factory()->create();
    [$provider, $service] = createBookableProvider();

    ProviderAvailability::query()->where('provider_id', $provider->id)->update([
        'day_of_week' => 0,
        'start_time' => '00:00:00',
        'end_time' => '04:00:00',
    ]);

    $response = $this->actingAs($customer)->postJson(route('appointments.store'), [
        'provider_id' => $provider->id,
        'service_id' => $service->id,
        'starts_at_local' => '2030-03-10 01:30:00',
        'user_timezone' => 'America/New_York',
    ]);

    $response->assertCreated();

    $appointment = Appointment::query()->firstOrFail();

    expect($appointment->starts_at_utc)->toBeInstanceOf(CarbonImmutable::class)
        ->and($appointment->starts_at_utc->timezoneName)->toBe('UTC');
});

it('prevents overlapping pending or confirmed appointments', function (): void {
    $customer = User::factory()->create();
    [$provider, $service] = createBookableProvider();

    Appointment::factory()->create([
        'provider_id' => $provider->id,
        'service_id' => $service->id,
        'status' => Appointment::STATUS_CONFIRMED,
        'starts_at_utc' => CarbonImmutable::parse('2030-04-01 14:00:00', 'UTC'),
        'ends_at_utc' => CarbonImmutable::parse('2030-04-01 15:00:00', 'UTC'),
    ]);

    $response = $this->actingAs($customer)->postJson(route('appointments.store'), [
        'provider_id' => $provider->id,
        'service_id' => $service->id,
        'starts_at_local' => '2030-04-01 10:30:00',
        'user_timezone' => 'America/New_York',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['starts_at_local']);
});

it('rejects bookings in expired availability windows', function (): void {
    $customer = User::factory()->create();
    [$provider, $service] = createBookableProvider();

    $response = $this->actingAs($customer)->postJson(route('appointments.store'), [
        'provider_id' => $provider->id,
        'service_id' => $service->id,
        'starts_at_local' => CarbonImmutable::now('America/New_York')->subDay()->format('Y-m-d H:i:s'),
        'user_timezone' => 'America/New_York',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['starts_at_local']);
});

it('rejects service duration mismatches against slot alignment', function (): void {
    $customer = User::factory()->create();
    [$provider, $service] = createBookableProvider();

    $service->update(['duration_minutes' => 45]);

    $response = $this->actingAs($customer)->postJson(route('appointments.store'), [
        'provider_id' => $provider->id,
        'service_id' => $service->id,
        'starts_at_local' => '2030-04-01 10:20:00',
        'user_timezone' => 'America/New_York',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['starts_at_local']);
});
