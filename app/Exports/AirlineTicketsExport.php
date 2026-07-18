<?php

namespace App\Exports;

use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AirlineTicketsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $tickets;

    public function __construct($tickets)
    {
        $this->tickets = $tickets;
    }

    public function collection()
    {
        return $this->tickets;
    }

    public function headings(): array
    {
        return [
            'Passenger',
            'Email',
            'Phone',
            'Passport No.',
            'Airline',
            'Flight No.',
            'Airline PNR',
            'Reservation PNR',
            'Passengers',
            'Ticket No.',
            'Origin',
            'Destination',
            'Flight Date',
            'Departure',
            'Arrival Date',
            'Arrival',
            'Status',
            'Purchased',
            'Purchase Date',
        ];
    }

    public function map($ticket): array
    {
        return [
            $ticket->passenger_name,
            $ticket->passenger_email,
            $ticket->passenger_phone,
            $ticket->passport_number,
            $ticket->airline_name,
            $ticket->flight_number,
            $ticket->pnr,
            $ticket->reservation_pnr,
            1 + count($ticket->additional_passengers ?? []),
            $ticket->ticket_number,
            $ticket->origin,
            $ticket->destination,
            $ticket->flight_date ? Carbon::parse($ticket->flight_date)->format('d M Y') : '',
            $ticket->departure_time,
            $ticket->arrival_date ? Carbon::parse($ticket->arrival_date)->format('d M Y') : '',
            $ticket->arrival_time,
            ucfirst($ticket->status),
            $ticket->is_purchased ? 'Yes' : 'No',
            $ticket->purchase_date ? Carbon::parse($ticket->purchase_date)->format('d M Y') : '',
        ];
    }
}
