<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Airline Tickets Report</title>
    <style>
        body { font-family: sans-serif; color: #1f2937; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 12px; }
        table.data th, table.data td { border: 1px solid #ddd; padding: 6px 7px; font-size: 10px; text-align: left; }
        table.data th { background-color: #f2f2f2; text-transform: uppercase; font-size: 8.5px; letter-spacing: 0.3px; }
        .strike { text-decoration: line-through; color: #ef4444; font-size: 9px; }
        .status { text-transform: capitalize; font-weight: bold; }
        .muted { color: #6b7280; font-size: 9px; }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    @php
        $fmt = fn ($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('d M Y') : '—';
    @endphp

    <h1 style="font-size:18px; margin:0;">Airline Tickets Report</h1>
    <p class="muted" style="margin:4px 0 0;">
        {{ $tickets->count() }} ticket(s)
        @if(!empty($filters['status'])) · Status: {{ ucfirst($filters['status']) }} @endif
        @if(!empty($filters['search'])) · Search: "{{ $filters['search'] }}" @endif
    </p>

    <table class="data">
        <thead>
            <tr>
                <th>Passenger</th>
                <th>Flight</th>
                <th>Route</th>
                <th>Flight Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($tickets as $ticket)
                <tr>
                    <td>
                        <strong>{{ $ticket->passenger_name }}</strong>
                        @if($ticket->passenger_email)<div class="muted">{{ $ticket->passenger_email }}</div>@endif
                    </td>
                    <td>
                        <strong>{{ $ticket->flight_number }}</strong>
                        <div class="muted">{{ $ticket->airline_name }}</div>
                        @if($ticket->pnr)<div class="muted">PNR: {{ $ticket->pnr }}</div>@endif
                    </td>
                    <td>{{ $ticket->origin }} &rarr; {{ $ticket->destination }}</td>
                    <td>
                        {{ $fmt($ticket->flight_date) }}
                        @if($ticket->original_flight_date)
                            <div class="strike">{{ $fmt($ticket->original_flight_date) }}</div>
                        @endif
                    </td>
                    <td class="status">{{ $ticket->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;">No tickets found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('pdfs.partials.report_footer')
</body>
</html>
