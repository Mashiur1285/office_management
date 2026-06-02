<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Ticket Confirmed – {{ $flightNumber }}</title>
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

        .wrapper {
            width: 100%;
            background-color: #f0f4f8;
            padding: 40px 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #166534 0%, #15803d 100%);
            padding: 32px 40px;
            text-align: center;
        }

        .header-badge {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 30px;
            padding: 6px 18px;
            color: #ffffff;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 16px;
        }

        .header-icon {
            display: inline-block;
            width: 56px;
            height: 56px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            line-height: 56px;
            font-size: 28px;
            margin-bottom: 12px;
        }

        .header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
        }

        .header p {
            margin: 6px 0 0;
            color: rgba(255,255,255,0.75);
            font-size: 14px;
        }

        .content {
            padding: 32px 40px;
        }

        .greeting {
            font-size: 17px;
            color: #111827;
            font-weight: 600;
            margin: 0 0 10px;
        }

        .intro {
            font-size: 15px;
            color: #4b5563;
            margin: 0 0 28px;
        }

        /* Route banner */
        .route-banner {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 20px 24px;
            margin-bottom: 24px;
            text-align: center;
        }

        .route-banner .airline {
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 12px;
        }

        .city {
            font-size: 26px;
            font-weight: 800;
            color: #111827;
            letter-spacing: 1px;
        }

        .route-arrow {
            margin: 0 16px;
            color: #9ca3af;
            font-size: 18px;
        }

        .flight-no {
            margin-top: 10px;
            font-size: 13px;
            color: #6b7280;
            font-weight: 500;
        }

        /* Confirmed date box */
        .date-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 4px solid #16a34a;
            border-radius: 10px;
            padding: 16px 20px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .date-box .date-icon {
            font-size: 24px;
            flex-shrink: 0;
        }

        .date-box .date-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #16a34a;
            margin-bottom: 4px;
        }

        .date-box .date-value {
            font-size: 20px;
            font-weight: 800;
            color: #15803d;
        }

        .date-box .date-sub {
            font-size: 12px;
            color: #4ade80;
            margin-top: 2px;
        }

        /* Info rows */
        .info-row {
            display: flex;
            gap: 8px;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
        }

        .info-row:last-child { border-bottom: none; }

        .info-label {
            width: 140px;
            color: #6b7280;
            flex-shrink: 0;
        }

        .info-value {
            color: #111827;
            font-weight: 500;
        }

        /* Footer */
        .footer {
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            padding: 24px 40px;
            text-align: center;
            font-size: 13px;
            color: #9ca3af;
        }

        @media only screen and (max-width: 620px) {
            .content, .header, .footer { padding-left: 20px; padding-right: 20px; }
            .city { font-size: 20px; }
            .date-box { flex-direction: column; text-align: center; gap: 8px; }
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="container">

            <!-- Header -->
            <div class="header">
                <div class="header-badge">✓ &nbsp;Booking Confirmed</div>
                <div class="header-icon">✈</div>
                <h1>Your Ticket is Confirmed</h1>
                <p>{{ config('app.name') }}</p>
            </div>

            <!-- Content -->
            <div class="content">
                <p class="greeting">Dear {{ $passengerName }},</p>
                <p class="intro">
                    Great news! Your flight ticket has been successfully booked. Please find your booking details below and keep this email for your records.
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

                <!-- Confirmed Date -->
                <div class="date-box">
                    <div class="date-icon">📅</div>
                    <div>
                        <div class="date-label">Flight Date</div>
                        <div class="date-value">{{ $flightDate }}</div>
                        @if($departureTime)
                            <div class="date-sub">Departure: {{ $departureTime }}</div>
                        @endif
                    </div>
                </div>

                <!-- Info Rows -->
                <div>
                    <div class="info-row">
                        <span class="info-label">Passenger</span>
                        <span class="info-value">{{ $passengerName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Airline</span>
                        <span class="info-value">{{ $airlineName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Flight No.</span>
                        <span class="info-value">{{ $flightNumber }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Route</span>
                        <span class="info-value">{{ $origin }} → {{ $destination }}</span>
                    </div>
                    @if($pnr)
                    <div class="info-row">
                        <span class="info-label">PNR</span>
                        <span class="info-value">{{ $pnr }}</span>
                    </div>
                    @endif
                    @if($ticketNumber)
                    <div class="info-row">
                        <span class="info-label">Ticket Number</span>
                        <span class="info-value">{{ $ticketNumber }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p style="margin:0 0 4px;">
                    <strong>{{ config('app.name') }}</strong>
                </p>
                <p style="margin:0;">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                </p>
            </div>

        </div>
    </div>
</body>

</html>
