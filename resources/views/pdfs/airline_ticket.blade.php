<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket {{ $ticket->pnr ?? $ticket->flight_number }}</title>
    <style>
        body { font-family: sans-serif; color: #1f2937; font-size: 12px; }
        h1, h2, h3 { margin: 0; }
        .section { margin-top: 16px; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px 14px; }
        .section-title { font-size: 10px; font-weight: bold; color: #6b7280; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .route { text-align: center; background: #f8fafc; border-radius: 6px; padding: 14px; margin-bottom: 12px; }
        .route .code { font-size: 22px; font-weight: 800; color: #111827; }
        .route .arrow { color: #1d4ed8; font-size: 16px; padding: 0 14px; }
        .route .label { font-size: 9px; color: #6b7280; text-transform: uppercase; }
        table.grid { width: 100%; border-collapse: collapse; }
        table.grid td { padding: 5px 8px; vertical-align: top; width: 33%; }
        .k { font-size: 8.5px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; }
        .v { font-size: 12px; font-weight: bold; color: #111827; margin-top: 2px; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: bold; text-transform: capitalize; }
        .badge-confirmed { background: #eff6ff; color: #1d4ed8; }
        .badge-rescheduled { background: #fffbeb; color: #b45309; }
        .badge-cancelled { background: #fef2f2; color: #b91c1c; }
        .badge-flown { background: #f3f4f6; color: #4b5563; }
        .strike { text-decoration: line-through; color: #ef4444; }
        ul { margin: 4px 0 0; padding-left: 16px; }
        li { margin-bottom: 3px; }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    @php
        $fmt = fn ($d) => $d ? \Illuminate\Support\Carbon::parse($d)->format('d M Y') : '—';
        $passengers = array_merge(
            [[
                'passenger_name'  => $ticket->passenger_name,
                'passport_number' => $ticket->passport_number,
                'ticket_number'   => $ticket->ticket_number,
            ]],
            $ticket->additional_passengers ?? []
        );
    @endphp

    <table style="width:100%; border:none;">
        <tr>
            <td style="border:none; vertical-align:middle;">
                <h1 style="font-size:18px; color:#111827;">{{ $ticket->passenger_name }}</h1>
                <div style="font-size:11px; color:#6b7280; margin-top:4px;">
                    {{ $ticket->airline_name }} &nbsp;·&nbsp; Flight {{ $ticket->flight_number }}
                    @if($ticket->pnr) &nbsp;·&nbsp; PNR: {{ $ticket->pnr }} @endif
                </div>
            </td>
            <td style="border:none; text-align:right; vertical-align:middle;">
                <span class="badge badge-{{ $ticket->status }}">{{ $ticket->status }}</span>
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Flight Information</div>
        <table style="width:100%; border:none;" class="route-tbl">
            <tr>
                <td style="border:none; text-align:center; width:40%;">
                    <div class="route">
                        <div class="code">{{ $ticket->origin }}</div>
                        <div class="label">Origin</div>
                    </div>
                </td>
                <td style="border:none; text-align:center; width:20%; color:#1d4ed8; font-size:18px;">&#9992;</td>
                <td style="border:none; text-align:center; width:40%;">
                    <div class="route">
                        <div class="code">{{ $ticket->destination }}</div>
                        <div class="label">Destination</div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="grid">
            <tr>
                <td><div class="k">Airline</div><div class="v">{{ $ticket->airline_name }}</div></td>
                <td><div class="k">Flight No.</div><div class="v">{{ $ticket->flight_number }}</div></td>
                <td><div class="k">Airline PNR</div><div class="v">{{ $ticket->pnr ?: '—' }}</div></td>
            </tr>
            <tr>
                <td><div class="k">Reservation PNR</div><div class="v">{{ $ticket->reservation_pnr ?: '—' }}</div></td>
                <td><div class="k">Class</div><div class="v">{{ $ticket->ticket_class ?: '—' }}</div></td>
                <td><div class="k">Issue Date</div><div class="v">{{ $fmt($ticket->issue_date) }}</div></td>
            </tr>
            @if($ticket->airport_name || $ticket->terminal || $ticket->gate)
            <tr>
                <td><div class="k">Airport</div><div class="v">{{ $ticket->airport_name ?: '—' }}</div></td>
                <td><div class="k">Terminal</div><div class="v">{{ $ticket->terminal ?: '—' }}</div></td>
                <td><div class="k">Gate</div><div class="v">{{ $ticket->gate ?: '—' }}</div></td>
            </tr>
            @endif
        </table>

        @if($ticket->has_transit && !empty($ticket->transits))
            <div style="margin-top:10px;">
                <div class="k" style="color:#b45309;">Transit</div>
                <ul>
                    @foreach($ticket->transits as $t)
                        <li>{{ $t['at'] ?? '' }}
                            @if(!empty($t['date'])) — {{ $fmt($t['date']) }} @endif
                            @if(!empty($t['time'])) {{ $t['time'] }} @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Schedule</div>
        <table class="grid">
            <tr>
                <td>
                    <div class="k">Flight Date</div>
                    <div class="v">{{ $fmt($ticket->flight_date) }}</div>
                    @if($ticket->original_flight_date)
                        <div class="strike" style="font-size:10px; margin-top:2px;">{{ $fmt($ticket->original_flight_date) }}</div>
                    @endif
                </td>
                <td><div class="k">Departure</div><div class="v">{{ $ticket->departure_time ?: '—' }}</div></td>
                <td><div class="k">Arrival Date</div><div class="v">{{ $fmt($ticket->arrival_date) }}</div></td>
            </tr>
            <tr>
                <td><div class="k">Arrival Time</div><div class="v">{{ $ticket->arrival_time ?: '—' }}</div></td>
                <td>
                    <div class="k">Purchased</div>
                    <div class="v">{{ $ticket->is_purchased ? 'Yes' : 'No' }}</div>
                </td>
                @if($ticket->is_purchased && $ticket->purchase_date)
                    <td><div class="k">Purchase Date</div><div class="v">{{ $fmt($ticket->purchase_date) }}</div></td>
                @endif
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Passenger Info @if(count($passengers) > 1)({{ count($passengers) }} passengers)@endif</div>
        <table class="data" style="width:100%; border-collapse:collapse; margin-bottom:10px;">
            <thead>
                <tr>
                    <th style="border:1px solid #e5e7eb; padding:5px 7px; background:#f8fafc; font-size:9px; text-transform:uppercase; text-align:left; width:8%;">#</th>
                    <th style="border:1px solid #e5e7eb; padding:5px 7px; background:#f8fafc; font-size:9px; text-transform:uppercase; text-align:left;">Full Name</th>
                    <th style="border:1px solid #e5e7eb; padding:5px 7px; background:#f8fafc; font-size:9px; text-transform:uppercase; text-align:left;">Passport No.</th>
                    <th style="border:1px solid #e5e7eb; padding:5px 7px; background:#f8fafc; font-size:9px; text-transform:uppercase; text-align:left;">Ticket No.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($passengers as $i => $p)
                    <tr>
                        <td style="border:1px solid #e5e7eb; padding:5px 7px;">{{ $i + 1 }}</td>
                        <td style="border:1px solid #e5e7eb; padding:5px 7px;">{{ $p['passenger_name'] ?? '—' }}</td>
                        <td style="border:1px solid #e5e7eb; padding:5px 7px;">{{ $p['passport_number'] ?? '' ?: '—' }}</td>
                        <td style="border:1px solid #e5e7eb; padding:5px 7px;">{{ $p['ticket_number'] ?? '' ?: '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <table class="grid">
            <tr>
                <td><div class="k">Phone</div><div class="v">{{ $ticket->passenger_phone ?: '—' }}</div></td>
                <td><div class="k">Email</div><div class="v">{{ $ticket->passenger_email ?: '—' }}</div></td>
                @if($ticket->client)
                    <td><div class="k">Linked Client</div><div class="v">{{ $ticket->client->name }}</div></td>
                @endif
            </tr>
        </table>
    </div>

    @if($ticket->hand_luggage_kg || $ticket->cabin_luggage_kg || $ticket->complementary_food)
    <div class="section">
        <div class="section-title">Luggage &amp; Amenities</div>
        <table class="grid">
            <tr>
                <td>
                    <div class="k">Hand Luggage</div>
                    <div class="v">{{ $ticket->hand_luggage_kg ? $ticket->hand_luggage_kg . ' kg' : '—' }}
                        @if($ticket->hand_luggage_max_weight)<span style="font-weight:normal; color:#6b7280;">(max {{ $ticket->hand_luggage_max_weight }} kg)</span>@endif
                    </div>
                </td>
                <td>
                    <div class="k">Cabin Luggage</div>
                    <div class="v">{{ $ticket->cabin_luggage_kg ? $ticket->cabin_luggage_kg . ' kg' : '—' }}
                        @if($ticket->cabin_luggage_max_weight)<span style="font-weight:normal; color:#6b7280;">(max {{ $ticket->cabin_luggage_max_weight }} kg)</span>@endif
                    </div>
                </td>
                <td><div class="k">Complementary Food</div><div class="v">{{ $ticket->complementary_food ? 'Yes' : 'No' }}</div></td>
            </tr>
        </table>
    </div>
    @endif

    @if($ticket->free_cancellation_days !== null || $ticket->partial_cancellation_days !== null || $ticket->partial_cancellation_percent !== null || $ticket->no_refund_hours !== null)
    <div class="section">
        <div class="section-title">Cancellation Policy</div>
        <ul>
            @if($ticket->free_cancellation_days !== null)
                <li>Free cancellation before <strong>{{ $ticket->free_cancellation_days }}</strong> days.</li>
            @endif
            @if($ticket->partial_cancellation_days !== null || $ticket->partial_cancellation_percent !== null)
                <li>Partial cancellation before <strong>{{ $ticket->partial_cancellation_days ?? '—' }}</strong> days, charged <strong>{{ $ticket->partial_cancellation_percent ?? '—' }}%</strong> of the ticket fee.</li>
            @endif
            @if($ticket->no_refund_hours !== null)
                <li>No refund before <strong>{{ $ticket->no_refund_hours }}</strong> hours.</li>
            @endif
        </ul>
    </div>
    @endif

    @if($ticket->notes)
    <div class="section">
        <div class="section-title">Notes</div>
        <div style="font-size:11px; color:#4b5563; white-space:pre-line;">{{ $ticket->notes }}</div>
    </div>
    @endif

    @include('pdfs.partials.report_footer')
</body>
</html>
