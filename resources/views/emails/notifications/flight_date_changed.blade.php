<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Flight Date Change Notice – {{ $flightNumber }}</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f0f4f8;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #374151;
            line-height: 1.6;
        }
        table { border-collapse: collapse; }
        .wrapper { width: 100%; background-color: #f0f4f8; padding: 40px 0; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.08); overflow: hidden; }
        .header { background: linear-gradient(135deg, #b45309 0%, #d97706 100%); padding: 32px 40px; text-align: center; }
        .header-badge { display: inline-block; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); border-radius: 30px; padding: 6px 18px; color: #ffffff; font-size: 12px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 16px; }
        .header-icon { display: inline-block; width: 56px; height: 56px; background: rgba(255,255,255,0.15); border-radius: 50%; line-height: 56px; font-size: 28px; margin-bottom: 12px; }
        .header h1 { margin: 0; color: #ffffff; font-size: 22px; font-weight: 700; }
        .header p { margin: 6px 0 0; color: rgba(255,255,255,0.8); font-size: 14px; }
        .content { padding: 32px 40px; }
        .greeting { font-size: 17px; color: #111827; font-weight: 600; margin: 0 0 10px; }
        .intro { font-size: 15px; color: #4b5563; margin: 0 0 28px; }

        .route-banner { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 20px 24px; margin-bottom: 24px; text-align: center; }
        .route-banner .airline { font-size: 13px; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 12px; }
        .city { font-size: 26px; font-weight: 800; color: #111827; letter-spacing: 1px; }
        .route-arrow { margin: 0 16px; color: #9ca3af; font-size: 18px; }
        .flight-no { margin-top: 10px; font-size: 13px; color: #6b7280; font-weight: 500; }

        .date-change-grid { display: table; width: 100%; border-radius: 10px; overflow: hidden; margin-bottom: 24px; border: 1px solid #e5e7eb; }
        .date-cell { display: table-cell; width: 50%; padding: 20px; vertical-align: top; }
        .date-cell.old { background: #fef2f2; border-right: 1px solid #fecaca; }
        .date-cell.new { background: #f0fdf4; }
        .date-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px; }
        .date-cell.old .date-label { color: #dc2626; }
        .date-cell.new .date-label { color: #16a34a; }
        .date-value { font-size: 18px; font-weight: 700; }
        .date-cell.old .date-value { color: #dc2626; text-decoration: line-through; }
        .date-cell.new .date-value { color: #15803d; }
        .date-sub { font-size: 12px; margin-top: 4px; }
        .date-cell.old .date-sub { color: #f87171; }
        .date-cell.new .date-sub { color: #4ade80; }

        .section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; margin: 24px 0 8px; }
        .info-row { display: flex; gap: 8px; padding: 10px 0; border-bottom: 1px solid #f3f4f6; font-size: 14px; }
        .info-row:last-child { border-bottom: none; }
        .info-label { width: 150px; color: #6b7280; flex-shrink: 0; }
        .info-value { color: #111827; font-weight: 500; }

        .transit-box { background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; padding: 12px 16px; margin-top: 8px; font-size: 13px; }
        .transit-item { padding: 6px 0; border-bottom: 1px dashed #fde68a; }
        .transit-item:last-child { border-bottom: none; }

        .notice { background: #fffbeb; border: 1px solid #fde68a; border-left: 4px solid #f59e0b; border-radius: 8px; padding: 14px 18px; font-size: 14px; color: #92400e; margin-top: 24px; }
        .footer { background: #f9fafb; border-top: 1px solid #e5e7eb; padding: 24px 40px; text-align: center; font-size: 13px; color: #9ca3af; }

        @media only screen and (max-width: 620px) {
            .content, .header, .footer { padding-left: 20px; padding-right: 20px; }
            .date-change-grid { display: block; }
            .date-cell { display: block; width: 100%; box-sizing: border-box; }
            .date-cell.old { border-right: none; border-bottom: 1px solid #fecaca; }
            .city { font-size: 20px; }
            .info-label { width: 120px; }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="container">

            <!-- Header -->
            <div class="header">
                <div class="header-badge">⚠ &nbsp;Date Changed</div>
                <div class="header-icon">✈</div>
                <h1>Flight Date Change Notice</h1>
                <p>{{ config('app.name') }}</p>
            </div>

            <!-- Content -->
            <div class="content">
                <p class="greeting">Dear {{ $passengerName }},</p>
                <p class="intro">
                    We would like to inform you that the flight date for your booking has been updated.
                    Please review the new schedule details below and keep this email for your records.
                </p>

                <!-- Route Banner -->
                <div class="route-banner">
                    <div class="airline">{{ $airlineName }}</div>
                    <div>
                        <span class="city">{{ $origin }}</span>
                        <span class="route-arrow">&#9992;</span>
                        <span class="city">{{ $destination }}</span>
                    </div>
                    <div class="flight-no">
                        Flight {{ $flightNumber }}
                        @if($pnr) &nbsp;·&nbsp; PNR: <strong>{{ $pnr }}</strong> @endif
                        @if($ticketNumber) &nbsp;·&nbsp; Ticket: <strong>{{ $ticketNumber }}</strong> @endif
                    </div>
                </div>

                <!-- Date Change Box -->
                <div class="date-change-grid">
                    <div class="date-cell old">
                        <div class="date-label">Previous Date</div>
                        <div class="date-value">{{ $oldDate }}</div>
                        <div class="date-sub">Cancelled</div>
                    </div>
                    <div class="date-cell new">
                        <div class="date-label">New Date</div>
                        <div class="date-value">{{ $newDate }}</div>
                        <div class="date-sub">
                            @if($departureTime) Departure: {{ $departureTime }}
                            @else Please reconfirm departure time
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Passenger Info -->
                <div class="section-title">Passenger Information</div>
                <div>
                    <div class="info-row">
                        <span class="info-label">Full Name</span>
                        <span class="info-value">{{ $passengerName }}</span>
                    </div>
                    @if(!empty($passportNumber))
                    <div class="info-row">
                        <span class="info-label">Passport Number</span>
                        <span class="info-value">{{ $passportNumber }}</span>
                    </div>
                    @endif
                </div>

                <!-- Ticket Details -->
                <div class="section-title">Ticket Details</div>
                <div>
                    @if(!empty($ticketNumber))
                    <div class="info-row">
                        <span class="info-label">Ticket Number</span>
                        <span class="info-value">{{ $ticketNumber }}</span>
                    </div>
                    @endif
                    @if(!empty($pnr))
                    <div class="info-row">
                        <span class="info-label">PNR</span>
                        <span class="info-value">{{ $pnr }}</span>
                    </div>
                    @endif
                    @if(!empty($issueDate))
                    <div class="info-row">
                        <span class="info-label">Issue Date</span>
                        <span class="info-value">{{ $issueDate }}</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="info-label">Airline</span>
                        <span class="info-value">{{ $airlineName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Flight No.</span>
                        <span class="info-value">{{ $flightNumber }}</span>
                    </div>
                </div>

                <!-- Flight Information -->
                <div class="section-title">Flight Information</div>
                <div>
                    <div class="info-row">
                        <span class="info-label">From</span>
                        <span class="info-value">{{ $origin }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">New Departure</span>
                        <span class="info-value">{{ $newDate }}@if(!empty($departureTime)) &nbsp;·&nbsp; {{ $departureTime }}@endif</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">To</span>
                        <span class="info-value">{{ $destination }}</span>
                    </div>
                    @if(!empty($arrivalDate))
                    <div class="info-row">
                        <span class="info-label">Arrival Date</span>
                        <span class="info-value">{{ $arrivalDate }}@if(!empty($arrivalTime)) &nbsp;·&nbsp; {{ $arrivalTime }}@endif</span>
                    </div>
                    @endif
                </div>

                <!-- Transit Info -->
                @if(!empty($transits) && count($transits) > 0)
                <div class="section-title">Transit Information</div>
                <div class="transit-box">
                    @foreach($transits as $i => $transit)
                    <div class="transit-item">
                        <strong>Transit {{ $i + 1 }}:</strong> {{ $transit['at'] ?? '—' }}
                        @if(!empty($transit['date'])) &nbsp;·&nbsp; {{ $transit['date'] }} @endif
                        @if(!empty($transit['time'])) &nbsp;·&nbsp; {{ $transit['time'] }} @endif
                    </div>
                    @endforeach
                </div>
                @endif

                <!-- Notice -->
                <div class="notice">
                    ⚠️ &nbsp;Please contact us or the airline directly if you have any concerns
                    about this change. Ensure you verify your seat and check-in information for the new date.
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p style="margin:0 0 4px;"><strong>{{ config('app.name') }}</strong></p>
                <p style="margin:0;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            </div>

        </div>
    </div>
</body>

</html>
