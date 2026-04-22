<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Services\Bookings\AppointmentBookingService;
use Illuminate\Http\JsonResponse;

class AppointmentController extends Controller
{
    public function store(
        StoreAppointmentRequest $request,
        AppointmentBookingService $appointmentBookingService,
    ): JsonResponse {
        $appointment = $appointmentBookingService->create($request->validated());

        return response()->json([
            'id' => $appointment->id,
            'status' => $appointment->status,
            'starts_at_utc' => $appointment->starts_at_utc?->toIso8601String(),
            'ends_at_utc' => $appointment->ends_at_utc?->toIso8601String(),
        ], 201);
    }
}
