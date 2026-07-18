<?php

namespace App\Http\Controllers;

use App\Exports\AirlineTicketsExport;
use App\Models\AirlineTicket;
use App\Models\Client;
use App\Notifications\FlightDateChangedNotification;
use App\Notifications\TicketCreatedNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class AirlineTicketController extends Controller
{
    public function index(Request $request): Response
    {
        $tickets = $this->filteredQuery($request)->paginate(20)->withQueryString();

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
            'passenger_email' => ['nullable', 'email', 'max:255'],
            'passenger_phone' => ['nullable', 'string', 'max:30'],
            'passport_number' => ['nullable', 'string', 'max:50'],
            'airline_name'    => ['required', 'string', 'max:255'],
            'flight_number'   => ['required', 'string', 'max:20'],
            'pnr'             => ['nullable', 'string', 'max:20'],
            'reservation_pnr' => ['nullable', 'string', 'max:20'],
            'ticket_number'   => ['nullable', 'string', 'max:50'],
            'additional_passengers'                   => ['nullable', 'array'],
            'additional_passengers.*.passenger_name'  => ['nullable', 'string', 'max:255'],
            'additional_passengers.*.passport_number' => ['nullable', 'string', 'max:50'],
            'additional_passengers.*.ticket_number'   => ['nullable', 'string', 'max:50'],
            'ticket_class'    => ['nullable', 'string', 'max:50'],
            'hand_luggage_kg'          => ['nullable', 'string', 'max:50'],
            'hand_luggage_max_weight'  => ['nullable', 'string', 'max:50'],
            'cabin_luggage_kg'         => ['nullable', 'string', 'max:50'],
            'cabin_luggage_max_weight' => ['nullable', 'string', 'max:50'],
            'complementary_food'       => ['boolean'],
            'issue_date'      => ['nullable', 'date'],
            'origin'          => ['required', 'string', 'max:100'],
            'destination'     => ['required', 'string', 'max:100'],
            'airport_name'    => ['nullable', 'string', 'max:150'],
            'terminal'        => ['nullable', 'string', 'max:50'],
            'gate'            => ['nullable', 'string', 'max:50'],
            'flight_date'     => ['required', 'date'],
            'departure_time'  => ['nullable', 'date_format:H:i'],
            'arrival_date'    => ['nullable', 'date'],
            'arrival_time'    => ['nullable', 'date_format:H:i'],
            'has_transit'     => ['boolean'],
            'transits'        => ['nullable', 'array'],
            'transits.*.at'   => ['nullable', 'string', 'max:100'],
            'transits.*.date' => ['nullable', 'date'],
            'transits.*.time' => ['nullable', 'date_format:H:i'],
            'status'          => ['required', 'in:confirmed,rescheduled,cancelled,flown'],
            'is_purchased'    => ['boolean'],
            'purchase_date'   => ['nullable', 'date'],
            'notes'           => ['nullable', 'string'],
            'free_cancellation_days'       => ['nullable', 'integer', 'min:0'],
            'partial_cancellation_days'    => ['nullable', 'integer', 'min:0'],
            'partial_cancellation_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'no_refund_hours'              => ['nullable', 'integer', 'min:0'],
        ]);

        $data['created_by'] = Auth::id();

        $ticket = AirlineTicket::create($data);

        if ($ticket->passenger_email) {
            Notification::route('mail', $ticket->passenger_email)
                ->notify(new TicketCreatedNotification(
                    passengerName:  $ticket->passenger_name,
                    airlineName:    $ticket->airline_name,
                    flightNumber:   $ticket->flight_number,
                    origin:         $ticket->origin,
                    destination:    $ticket->destination,
                    flightDate:     Carbon::parse($ticket->flight_date)->format('d M Y'),
                    departureTime:  $ticket->departure_time,
                    pnr:            $ticket->pnr,
                    ticketNumber:   $ticket->ticket_number,
                    passportNumber: $ticket->passport_number,
                    issueDate:      $ticket->issue_date ? Carbon::parse($ticket->issue_date)->format('d M Y') : null,
                    arrivalDate:    $ticket->arrival_date ? Carbon::parse($ticket->arrival_date)->format('d M Y') : null,
                    arrivalTime:    $ticket->arrival_time,
                    transits:       $ticket->transits,
                    reservationPnr: $ticket->reservation_pnr,
                    passengers:     $ticket->allPassengers(),
                ));
        }

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
            'passenger_email' => ['nullable', 'email', 'max:255'],
            'passenger_phone' => ['nullable', 'string', 'max:30'],
            'passport_number' => ['nullable', 'string', 'max:50'],
            'airline_name'    => ['required', 'string', 'max:255'],
            'flight_number'   => ['required', 'string', 'max:20'],
            'pnr'             => ['nullable', 'string', 'max:20'],
            'reservation_pnr' => ['nullable', 'string', 'max:20'],
            'ticket_number'   => ['nullable', 'string', 'max:50'],
            'additional_passengers'                   => ['nullable', 'array'],
            'additional_passengers.*.passenger_name'  => ['nullable', 'string', 'max:255'],
            'additional_passengers.*.passport_number' => ['nullable', 'string', 'max:50'],
            'additional_passengers.*.ticket_number'   => ['nullable', 'string', 'max:50'],
            'ticket_class'    => ['nullable', 'string', 'max:50'],
            'hand_luggage_kg'          => ['nullable', 'string', 'max:50'],
            'hand_luggage_max_weight'  => ['nullable', 'string', 'max:50'],
            'cabin_luggage_kg'         => ['nullable', 'string', 'max:50'],
            'cabin_luggage_max_weight' => ['nullable', 'string', 'max:50'],
            'complementary_food'       => ['boolean'],
            'issue_date'      => ['nullable', 'date'],
            'origin'          => ['required', 'string', 'max:100'],
            'destination'     => ['required', 'string', 'max:100'],
            'airport_name'    => ['nullable', 'string', 'max:150'],
            'terminal'        => ['nullable', 'string', 'max:50'],
            'gate'            => ['nullable', 'string', 'max:50'],
            'flight_date'     => ['required', 'date'],
            'departure_time'  => ['nullable', 'date_format:H:i'],
            'arrival_date'    => ['nullable', 'date'],
            'arrival_time'    => ['nullable', 'date_format:H:i'],
            'has_transit'     => ['boolean'],
            'transits'        => ['nullable', 'array'],
            'transits.*.at'   => ['nullable', 'string', 'max:100'],
            'transits.*.date' => ['nullable', 'date'],
            'transits.*.time' => ['nullable', 'date_format:H:i'],
            'status'          => ['required', 'in:confirmed,rescheduled,cancelled,flown'],
            'is_purchased'    => ['boolean'],
            'purchase_date'   => ['nullable', 'date'],
            'notes'           => ['nullable', 'string'],
            'free_cancellation_days'       => ['nullable', 'integer', 'min:0'],
            'partial_cancellation_days'    => ['nullable', 'integer', 'min:0'],
            'partial_cancellation_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'no_refund_hours'              => ['nullable', 'integer', 'min:0'],
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

        if ($airlineTicket->passenger_email) {
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
                    ticketNumber:   $airlineTicket->ticket_number,
                    passportNumber: $airlineTicket->passport_number,
                    issueDate:      $airlineTicket->issue_date ? Carbon::parse($airlineTicket->issue_date)->format('d M Y') : null,
                    arrivalDate:    $airlineTicket->arrival_date ? Carbon::parse($airlineTicket->arrival_date)->format('d M Y') : null,
                    arrivalTime:    $airlineTicket->arrival_time,
                    transits:       $airlineTicket->transits,
                    reservationPnr: $airlineTicket->reservation_pnr,
                    passengers:     $airlineTicket->allPassengers(),
                ));
        }

        return back()->with('success', "Flight rescheduled. Notification sent to {$airlineTicket->passenger_email}.");
    }

    /**
     * PDF for a single ticket. ?action=download forces download, otherwise streams inline (for printing).
     */
    public function ticketPdf(Request $request, AirlineTicket $airlineTicket)
    {
        $airlineTicket->load('client', 'creator');

        $pdf = Pdf::loadView('pdfs.airline_ticket', ['ticket' => $airlineTicket]);
        $fileName = 'ticket-' . ($airlineTicket->pnr ?: $airlineTicket->id) . '.pdf';

        if ($request->query('action') === 'download') {
            return $pdf->download($fileName);
        }

        return $pdf->stream($fileName);
    }

    /**
     * Export the (filtered) ticket list as excel, pdf (download) or print (inline pdf).
     */
    public function export(Request $request, string $type)
    {
        $tickets = $this->filteredQuery($request)->get();
        $date    = now()->format('Y-m-d');

        if ($type === 'excel') {
            return Excel::download(new AirlineTicketsExport($tickets), "airline-tickets-{$date}.xlsx");
        }

        $pdf = Pdf::loadView('pdfs.airline_tickets_list', [
            'tickets' => $tickets,
            'filters' => $request->only(['search', 'status']),
        ]);

        if ($type === 'print') {
            return $pdf->stream("airline-tickets-{$date}.pdf");
        }

        return $pdf->download("airline-tickets-{$date}.pdf");
    }

    private function filteredQuery(Request $request)
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

        return $query;
    }
}
