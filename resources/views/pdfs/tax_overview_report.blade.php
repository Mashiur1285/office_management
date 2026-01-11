<!DOCTYPE html>
<html>
<head>
    <title>Tax Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    <h1>Tax Report</h1>
    <p>All Periods Summary</p>

    <table>
        <thead>
            <tr>
                <th>Metric</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Tax (All Periods)</td>
                <td>{{ $overallTotalTax }}</td>
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
                <th colspan="6">Per-Period Tax Summary</th>
            </tr>
            <tr>
                <th>Period</th>
                <th>Date Range</th>
                <th>Total Tax</th>
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
                    <td>{{ $period->total_tax }}</td>
                    <td>{{ $period->total_tax_payments }}</td>
                    <td>{{ $period->tax_balance }}</td>
                    <td>{{ ucfirst($period->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No accounting periods found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('pdfs.partials.report_footer')
</body>
</html>
