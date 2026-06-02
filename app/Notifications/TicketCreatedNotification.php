<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string  $passengerName,
        private readonly string  $airlineName,
        private readonly string  $flightNumber,
        private readonly string  $origin,
        private readonly string  $destination,
        private readonly string  $flightDate,
        private readonly ?string $departureTime = null,
        private readonly ?string $pnr = null,
        private readonly ?string $ticketNumber = null,
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Ticket Confirmed – {$this->flightNumber} | {$this->airlineName}")
            ->view('emails.notifications.ticket_created', [
                'passengerName' => $this->passengerName,
                'airlineName'   => $this->airlineName,
                'flightNumber'  => $this->flightNumber,
                'origin'        => $this->origin,
                'destination'   => $this->destination,
                'flightDate'    => $this->flightDate,
                'departureTime' => $this->departureTime,
                'pnr'           => $this->pnr,
                'ticketNumber'  => $this->ticketNumber,
            ]);
    }
}
