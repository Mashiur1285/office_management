<?php

namespace App\Http\Controllers;

use App\Models\AirlineTicket;
use App\Models\Client;
use App\Notifications\FlightDateChangedNotification;
use App\Notifications\TicketCreatedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class AirlineTicketController extends Controller
{
    public function index(Request $request): Response
    {
        $query = AirlineTicket::query()
            ->with('client')
            ->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('passenger_name', 'like', "%{$search}%")
                    ->orWhere('passenger_email', 'like', "%{$search}%")
                    ->orWhere('flight_number', 'like', "%{$search}%")
                    ->orWhere('pnr', 'like', "%{$search}%")
                    ->orWhere('ticket_number', 'like', "%{$search}%")
                    ->orWhere('airline_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $tickets = $query->paginate(20)->withQueryString();

        $stats = [
            'total'       => AirlineTicket::count(),
            'confirmed'   => AirlineTicket::where('status', 'confirmed')->count(),
            'rescheduled' => AirlineTicket::where('status', 'rescheduled')->count(),
            'cancelled'   => AirlineTicket::where('status', 'cancelled')->count(),
        ];

        return Inertia::render('AirlineTickets/Index', [
            'tickets' => $tickets,
            'stats'   => $stats,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        $clients = Client::select('id', 'name', 'email', 'mobile', 'passport_number')
            ->orderBy('name')
            ->get();

        return Inertia::render('AirlineTickets/Form', [
            'ticket'  => null,
            'clients' => $clients,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id'       => ['nullable', 'exists:clients,id'],
            'passenger_name'  => ['required', 'string', 'max:255'],
            'passenger_email' => ['required', 'email', 'max:255'],
            'passenger_phone' => ['nullable', 'string', 'max:30'],
            'airline_name'    => ['required', 'string', 'max:255'],
            'flight_number'   => ['required', 'string', 'max:20'],
            'pnr'             => ['nullable', 'string', 'max:20'],
            'ticket_number'   => ['nullable', 'string', 'max:50'],
            'origin'          => ['required', 'string', 'max:100'],
            'destination'     => ['required', 'string', 'max:100'],
            'flight_date'     => ['required', 'date'],
            'departure_time'  => ['nullable', 'date_format:H:i'],
            'status'          => ['required', 'in:confirmed,rescheduled,cancelled,flown'],
            'notes'           => ['nullable', 'string'],
        ]);

        $data['created_by'] = Auth::id();

        $ticket = AirlineTicket::create($data);

        Notification::route('mail', $ticket->passenger_email)
            ->notify(new TicketCreatedNotification(
                passengerName: $ticket->passenger_name,
                airlineName:   $ticket->airline_name,
                flightNumber:  $ticket->flight_number,
                origin:        $ticket->origin,
                destination:   $ticket->destination,
                flightDate:    Carbon::parse($ticket->flight_date)->format('d M Y'),
                departureTime: $ticket->departure_time,
                pnr:           $ticket->pnr,
                ticketNumber:  $ticket->ticket_number,
            ));

        return redirect()->route('airline-tickets.show', $ticket)
            ->with('success', 'Ticket created and confirmation email sent.');
    }

    public function show(AirlineTicket $airlineTicket): Response
    {
        $airlineTicket->load('client', 'creator');

        return Inertia::render('AirlineTickets/Show', [
            'ticket' => $airlineTicket,
        ]);
    }

    public function edit(AirlineTicket $airlineTicket): Response
    {
        $clients = Client::select('id', 'name', 'email', 'mobile', 'passport_number')
            ->orderBy('name')
            ->get();

        return Inertia::render('AirlineTickets/Form', [
            'ticket'  => $airlineTicket,
            'clients' => $clients,
        ]);
    }

    public function update(Request $request, AirlineTicket $airlineTicket): RedirectResponse
    {
        $data = $request->validate([
            'client_id'       => ['nullable', 'exists:clients,id'],
            'passenger_name'  => ['required', 'string', 'max:255'],
            'passenger_email' => ['required', 'email', 'max:255'],
            'passenger_phone' => ['nullable', 'string', 'max:30'],
            'airline_name'    => ['required', 'string', 'max:255'],
            'flight_number'   => ['required', 'string', 'max:20'],
            'pnr'             => ['nullable', 'string', 'max:20'],
            'ticket_number'   => ['nullable', 'string', 'max:50'],
            'origin'          => ['required', 'string', 'max:100'],
            'destination'     => ['required', 'string', 'max:100'],
            'flight_date'     => ['required', 'date'],
            'departure_time'  => ['nullable', 'date_format:H:i'],
            'status'          => ['required', 'in:confirmed,rescheduled,cancelled,flown'],
            'notes'           => ['nullable', 'string'],
        ]);

        $airlineTicket->update($data);

        return redirect()->route('airline-tickets.show', $airlineTicket)
            ->with('success', 'Ticket updated successfully.');
    }

    public function destroy(AirlineTicket $airlineTicket): RedirectResponse
    {
        $airlineTicket->delete();

        return redirect()->route('airline-tickets.index')
            ->with('success', 'Ticket deleted successfully.');
    }

    public function reschedule(Request $request, AirlineTicket $airlineTicket): RedirectResponse
    {
        $data = $request->validate([
            'new_flight_date' => ['required', 'date'],
            'new_departure_time' => ['nullable', 'date_format:H:i'],
            'reason'          => ['nullable', 'string', 'max:1000'],
        ]);

        $oldDate = Carbon::parse($airlineTicket->flight_date)->format('d M Y');

        $airlineTicket->update([
            'original_flight_date' => $airlineTicket->original_flight_date ?? $airlineTicket->flight_date,
            'flight_date'          => $data['new_flight_date'],
            'departure_time'       => $data['new_departure_time'] ?? $airlineTicket->departure_time,
            'status'               => 'rescheduled',
            'notes'                => $data['reason']
                ? trim(($airlineTicket->notes ? $airlineTicket->notes . "\n" : '') . '[Reschedule] ' . $data['reason'])
                : $airlineTicket->notes,
        ]);

        $newDate = Carbon::parse($airlineTicket->fresh()->flight_date)->format('d M Y');

        Notification::route('mail', $airlineTicket->passenger_email)
            ->notify(new FlightDateChangedNotification(
                passengerName:  $airlineTicket->passenger_name,
                airlineName:    $airlineTicket->airline_name,
                flightNumber:   $airlineTicket->flight_number,
                origin:         $airlineTicket->origin,
                destination:    $airlineTicket->destination,
                oldDate:        $oldDate,
                newDate:        $newDate,
                departureTime:  $data['new_departure_time'] ?? $airlineTicket->departure_time,
                pnr:            $airlineTicket->pnr,
            ));

        return back()->with('success', "Flight rescheduled. Notification sent to {$airlineTicket->passenger_email}.");
    }
}
