<!DOCTYPE html>
<html>
<head>
    <title>VAT Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    <h1>VAT Report</h1>

    <table>
        <thead>
            <tr>
                <th>Metric</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total VAT (All Periods)</td>
                <td>{{ $overallTotalVat }}</td>
            </tr>
            <tr>
                <td>Total Payments (All Periods)</td>
                <td>{{ $overallTotalPayments }}</td>
            </tr>
            <tr>
                <td>Outstanding Balance</td>
                <td>{{ $overallOutstandingBalance }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th colspan="6">Per-Period VAT Summary</th>
            </tr>
            <tr>
                <th>Period</th>
                <th>Date Range</th>
                <th>Total VAT</th>
                <th>Payments</th>
                <th>Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($periods as $period)
                <tr>
                    <td>{{ $period->name }}</td>
                    <td>{{ $period->start_date->format('Y-m-d') }} - {{ $period->end_date->format('Y-m-d') }}</td>
                    <td>{{ $period->total_vat }}</td>
                    <td>{{ $period->total_vat_payments }}</td>
                    <td>{{ $period->vat_balance }}</td>
                    <td>{{ $period->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No periods found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th colspan="4">Detailed Payment History</th>
            </tr>
            <tr>
                <th>Period</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Chalan Number</th>
            </tr>
        </thead>
        <tbody>
            @php $hasPayments = false; @endphp
            @foreach($periods as $period)
                @foreach($period->vatPayments as $payment)
                    @php $hasPayments = true; @endphp
                    <tr>
                        <td>{{ $period->name }}</td>
                        <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                        <td>{{ $payment->payment_amount }}</td>
                        <td>{{ $payment->chalan_number }}</td>
                    </tr>
                @endforeach
            @endforeach
            @if(!$hasPayments)
                <tr>
                    <td colspan="4" style="text-align: center;">No payments found.</td>
                </tr>
            @endif
        </tbody>
    </table>

    @include('pdfs.partials.report_footer')
</body>
</html>
