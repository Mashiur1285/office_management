<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FlightDateChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $passengerName,
        private readonly string $airlineName,
        private readonly string $flightNumber,
        private readonly string $origin,
        private readonly string $destination,
        private readonly string $oldDate,
        private readonly string $newDate,
        private readonly ?string $departureTime = null,
        private readonly ?string $pnr = null,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Flight Date Changed – {$this->flightNumber} | {$this->airlineName}")
            ->view('emails.notifications.flight_date_changed', [
                'passengerName'  => $this->passengerName,
                'airlineName'    => $this->airlineName,
                'flightNumber'   => $this->flightNumber,
                'origin'         => $this->origin,
                'destination'    => $this->destination,
                'oldDate'        => $this->oldDate,
                'newDate'        => $this->newDate,
                'departureTime'  => $this->departureTime,
                'pnr'            => $this->pnr,
            ]);
    }
}
