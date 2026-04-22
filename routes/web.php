<?php

declare(strict_types=1);

use App\Http\Controllers\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function (): void {
    Route::view('/provider/availability', 'provider.availability')->name('provider.availability');
    Route::view('/book', 'booking.index')->name('booking.index');

    Route::post('/appointments', [AppointmentController::class, 'store'])
        ->name('appointments.store');
});
