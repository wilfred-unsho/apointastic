<?php

declare(strict_types=1);

use App\Livewire\Admin\AppointmentApprovalsTable;
use App\Livewire\Admin\ProviderApprovalsTable;
use App\Models\Appointment;
use App\Models\Provider;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('blocks customers from admin and provider protected routes', function (): void {
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    $this->actingAs($customer)->get(route('admin.providers'))->assertForbidden();
    $this->actingAs($customer)->get(route('provider.availability'))->assertForbidden();
});

it('allows provider role to provider route but blocks admin route', function (): void {
    $providerUser = User::factory()->create();
    $providerUser->assignRole('provider');

    Provider::factory()->create([
        'user_id' => $providerUser->id,
        'approval_status' => Provider::STATUS_APPROVED,
    ]);

    $this->actingAs($providerUser)->get(route('provider.availability'))->assertOk();
    $this->actingAs($providerUser)->get(route('admin.appointments'))->assertForbidden();
});

it('registers policy abilities through seeded permissions', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $providerUser = User::factory()->create();
    $providerUser->assignRole('provider');

    expect($admin->can('provider.approve'))->toBeTrue()
        ->and($admin->can('appointment.approve'))->toBeTrue()
        ->and($providerUser->can('provider.approve'))->toBeFalse()
        ->and($providerUser->can('appointment.view'))->toBeTrue();
});

it('allows admin to approve or reject providers and appointments', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $providerOwner = User::factory()->create();
    $provider = Provider::factory()->create([
        'user_id' => $providerOwner->id,
        'approval_status' => Provider::STATUS_PENDING,
    ]);

    $customer = User::factory()->create();
    $service = Service::factory()->create(['provider_id' => $provider->id]);
    $appointment = Appointment::factory()->create([
        'customer_id' => $customer->id,
        'provider_id' => $provider->id,
        'service_id' => $service->id,
        'status' => Appointment::STATUS_PENDING,
    ]);

    $this->actingAs($admin);

    Livewire::test(ProviderApprovalsTable::class)
        ->call('approve', $provider->id);

    expect($provider->fresh()->approval_status)->toBe(Provider::STATUS_APPROVED);

    Livewire::test(AppointmentApprovalsTable::class)
        ->call('reject', $appointment->id);

    expect($appointment->fresh()->status)->toBe(Appointment::STATUS_CANCELLED);
});
