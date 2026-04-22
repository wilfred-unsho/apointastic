<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ProviderBookingDataController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function (): void {
    Route::get('/providers/{provider}/services', [ProviderBookingDataController::class, 'services'])
        ->name('api.providers.services');

    Route::get('/providers/{provider}/slots', [ProviderBookingDataController::class, 'slots'])
        ->name('api.providers.slots');

    Route::get('/providers/{provider}/availability-preview', [ProviderBookingDataController::class, 'weeklyAvailabilityPreview'])
        ->name('api.providers.availability-preview');
});
