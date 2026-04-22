<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Notifications\Channels\LogSmsChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $appointmentId,
        public readonly string $startsAtUtc,
        public readonly string $providerName,
        public readonly string $serviceName,
        public readonly string $type,
    ) {
        $this->onQueue('notifications');
    }

    /**
     * @param mixed $notifiable
     * @return array<int, string>
     */
    public function via(mixed $notifiable): array
    {
        return ['mail', LogSmsChannel::class];
    }

    /**
     * @param mixed $notifiable
     */
    public function toMail(mixed $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject($this->subject())
            ->greeting('Hello!')
            ->line("This is your {$this->type} notification for an upcoming appointment.")
            ->line("Service: {$this->serviceName}")
            ->line("Provider: {$this->providerName}")
            ->line("Starts at (UTC): {$this->startsAtUtc}")
            ->line('Thank you for booking with us.');
    }

    /**
     * @param mixed $notifiable
     * @return array<string, mixed>
     */
    public function toLogSms(mixed $notifiable): array
    {
        return [
            'appointment_id' => $this->appointmentId,
            'message' => sprintf(
                '[%s] %s with %s at %s UTC.',
                strtoupper($this->type),
                $this->serviceName,
                $this->providerName,
                $this->startsAtUtc,
            ),
        ];
    }

    private function subject(): string
    {
        return match ($this->type) {
            'confirmation' => 'Appointment confirmed',
            'reminder_24h' => 'Appointment reminder (24 hours)',
            'reminder_1h' => 'Appointment reminder (1 hour)',
            default => 'Appointment update',
        };
    }
}
