<?php

declare(strict_types=1);

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class LogSmsChannel
{
    /**
     * @param mixed $notifiable
     */
    public function send(mixed $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toLogSms')) {
            return;
        }

        $payload = $notification->toLogSms($notifiable);

        Log::channel(config('sms.log_channel', 'stack'))->info('Mock SMS notification sent.', [
            'notifiable_id' => data_get($notifiable, 'id'),
            'to' => data_get($notifiable, 'phone', 'unknown'),
            'message' => $payload['message'] ?? null,
            'appointment_id' => $payload['appointment_id'] ?? null,
        ]);
    }
}
