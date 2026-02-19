<!DOCTYPE html>
<html>
<head>
    <title>Refund Report</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #1f2937;
        }
        h1 {
            font-size: 18px;
            color: #be123c;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 11px;
            color: #6b7280;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
            text-align: left;
        }
        th {
            background-color: #fef2f2;
            color: #be123c;
            font-weight: 600;
            font-size: 10px;
            text-transform: uppercase;
        }
        td {
            font-size: 10px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: 600;
        }
        .amount {
            color: #be123c;
            font-weight: 600;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 600;
        }
        .badge-client {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .badge-agent {
            background-color: #f3e8ff;
            color: #7c3aed;
        }
        .summary-box {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 15px;
        }
        .summary-title {
            font-size: 10px;
            color: #be123c;
            margin-bottom: 4px;
        }
        .summary-value {
            font-size: 20px;
            font-weight: 700;
            color: #be123c;
        }
        .summary-count {
            font-size: 10px;
            color: #6b7280;
            margin-top: 4px;
        }
        tfoot td {
            background-color: #fef2f2;
            font-weight: 600;
        }
        .filters-info {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    <h1>Refund Report</h1>
    <p class="subtitle">Track all refunds issued to clients and agents</p>

    @if($startDate || $endDate)
        <p class="filters-info">
            Period:
            @if($startDate && $endDate)
                {{ $startDate }} to {{ $endDate }}
            @elseif($startDate)
                From {{ $startDate }}
            @else
                Until {{ $endDate }}
            @endif
        </p>
    @endif

    <div class="summary-box">
        <div class="summary-title">Total Refund Amount</div>
        <div class="summary-value">৳{{ number_format($totalRefund, 2) }}</div>
        <div class="summary-count">{{ count($refunds) }} refund{{ count($refunds) !== 1 ? 's' : '' }} recorded</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Refunded To</th>
                <th>Type</th>
                <th>Source</th>
                <th>Agent</th>
                <th class="text-right">Amount</th>
                <th>Method</th>
                <th>Notes</th>
                <th>Created By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($refunds as $refund)
                @php
                    $clientName = '';
                    $clientPhone = '';

                    if ($refund->payable && $refund->payable_type === 'App\\Models\\Client') {
                        $clientName = $refund->payable->name ?? '';
                        $clientPhone = $refund->payable->phone_number ?? '';
                    }

                    $refundedTo = '';
                    $refundedToType = '';
                    if ($refund->source === 'agent' && $refund->agent) {
                        $refundedTo = $refund->agent->name;
                        $refundedToType = 'Agent';
                    } else {
                        $refundedTo = $clientName ?: ($refund->payable?->name ?? '-');
                        $refundedToType = 'Client';
                    }
                @endphp
                <tr>
                    <td>{{ $refund->payment_date?->format('Y-m-d') ?? '-' }}</td>
                    <td>{{ $refundedTo }}</td>
                    <td>
                        <span class="badge {{ $refundedToType === 'Agent' ? 'badge-agent' : 'badge-client' }}">
                            {{ $refundedToType }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $refund->source === 'agent' ? 'badge-agent' : 'badge-client' }}">
                            {{ ucfirst($refund->source) }}
                        </span>
                    </td>
                    <td>{{ $refund->agent?->name ?? '-' }}</td>
                    <td class="text-right amount">৳{{ number_format($refund->amount, 2) }}</td>
                    <td>{{ $refund->payment_method ?? '-' }}</td>
                    <td>{{ $refund->notes ?? '-' }}</td>
                    <td>{{ $refund->creator?->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">No refunds found.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($refunds) > 0)
            <tfoot>
                <tr>
                    <td colspan="5" class="text-right font-bold">Total:</td>
                    <td class="text-right amount">৳{{ number_format($totalRefund, 2) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        @endif
    </table>

    @include('pdfs.partials.report_footer')
</body>
</html>
