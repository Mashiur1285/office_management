<!DOCTYPE html>
<html>
<head>
    <title>Tax Summary Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    <h1>Tax Summary Report</h1>
    <p>Period: {{ $period->name }}</p>

    <table>
        <thead>
            <tr>
                <th>Metric</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Tax</td>
                <td>{{ $totalTax }}</td>
            </tr>
            <tr>
                <td>Total Payments Made</td>
                <td>{{ $totalTaxPayments }}</td>
            </tr>
            <tr>
                <td>Remaining Balance</td>
                <td>{{ $taxBalance }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>Tax Type</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Current Tax</td>
                <td>{{ $taxByType['current'] ?? 0 }}</td>
            </tr>
            <tr>
                <td>Deferred Tax</td>
                <td>{{ $taxByType['deferred'] ?? 0 }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th colspan="4">Tax Payment History</th>
            </tr>
            <tr>
                <th>Date</th>
                <th>Amount</th>
                <th>Chalan Number</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
                <tr>
                    <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                    <td>{{ $payment->payment_amount }}</td>
                    <td>{{ $payment->chalan_number }}</td>
                    <td>{{ $payment->notes }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">No payments found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th colspan="5">Detailed Tax Entries</th>
            </tr>
            <tr>
                <th>Type</th>
                <th>Description</th>
                <th>Client</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $entry)
                <tr>
                    <td>{{ ucfirst($entry->tax_type) }}</td>
                    <td>{{ $entry->description }}</td>
                    <td>{{ optional($entry->client)->name ?? 'N/A' }}</td>
                    <td>{{ $entry->amount }}</td>
                    <td>{{ $entry->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No tax entries found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('pdfs.partials.report_footer')
</body>
</html>
