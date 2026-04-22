<?php

declare(strict_types=1);

use App\Console\Commands\CleanupPendingAppointmentsCommand;
use App\Jobs\Appointments\SendAppointmentConfirmationJob;
use App\Jobs\Appointments\SendAppointmentReminderJob;
use App\Models\Appointment;
use App\Models\Provider;
use App\Models\ProviderAvailability;
use App\Models\Service;
use App\Models\User;
use App\Notifications\AppointmentReminderNotification;
use App\Services\Bookings\AppointmentBookingService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

function seedBookingContext(): array {
    $provider = Provider::factory()->create(['timezone' => 'UTC']);
    $service = Service::factory()->create([
        'provider_id' => $provider->id,
        'duration_minutes' => 60,
    ]);

    ProviderAvailability::factory()->create([
        'provider_id' => $provider->id,
        'day_of_week' => 1,
        'start_time' => '09:00:00',
        'end_time' => '17:00:00',
        'timezone' => 'UTC',
    ]);

    $customer = User::factory()->create();

    return [$provider, $service, $customer];
}

it('dispatches confirmation and reminder jobs when appointment is created', function (): void {
    Queue::fake();

    [$provider, $service, $customer] = seedBookingContext();

    app(AppointmentBookingService::class)->create([
        'customer_id' => $customer->id,
        'provider_id' => $provider->id,
        'service_id' => $service->id,
        'starts_at_utc' => CarbonImmutable::parse('2030-04-01 10:00:00', 'UTC'),
        'ends_at_utc' => CarbonImmutable::parse('2030-04-01 11:00:00', 'UTC'),
        'user_timezone' => 'UTC',
    ]);

    Queue::assertPushed(SendAppointmentConfirmationJob::class, 1);
    Queue::assertPushed(SendAppointmentReminderJob::class, fn (SendAppointmentReminderJob $job): bool => $job->type === 'reminder_24h');
    Queue::assertPushed(SendAppointmentReminderJob::class, fn (SendAppointmentReminderJob $job): bool => $job->type === 'reminder_1h');
});

it('sends reminder notifications from queued jobs', function (): void {
    Notification::fake();

    [$provider, $service, $customer] = seedBookingContext();

    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'provider_id' => $provider->id,
        'service_id' => $service->id,
        'starts_at_utc' => CarbonImmutable::parse('2030-04-01 10:00:00', 'UTC'),
        'ends_at_utc' => CarbonImmutable::parse('2030-04-01 11:00:00', 'UTC'),
        'status' => Appointment::STATUS_PENDING,
    ]);

    (new SendAppointmentConfirmationJob($appointment->id))->handle();
    (new SendAppointmentReminderJob($appointment->id, 'reminder_24h'))->handle();

    Notification::assertSentTo($customer, AppointmentReminderNotification::class, 2);
});

it('cancels stale pending appointments via scheduler cleanup command', function (): void {
    [$provider, $service, $customer] = seedBookingContext();

    $oldPending = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'provider_id' => $provider->id,
        'service_id' => $service->id,
        'status' => Appointment::STATUS_PENDING,
        'created_at' => CarbonImmutable::now('UTC')->subMinutes(20),
    ]);

    $recentPending = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'provider_id' => $provider->id,
        'service_id' => $service->id,
        'status' => Appointment::STATUS_PENDING,
        'created_at' => CarbonImmutable::now('UTC')->subMinutes(5),
    ]);

    $this->artisan(CleanupPendingAppointmentsCommand::class)
        ->assertSuccessful();

    expect($oldPending->fresh()->status)->toBe(Appointment::STATUS_CANCELLED)
        ->and($recentPending->fresh()->status)->toBe(Appointment::STATUS_PENDING);
});
