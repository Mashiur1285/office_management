<!DOCTYPE html>
<html>
<head>
    <title>Tax Management Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    <h1>Tax Management Report</h1>
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
                <td>Net Profit Before Tax</td>
                <td>{{ $netProfitBeforeTax }}</td>
            </tr>
            <tr>
                <td>Total Current Tax</td>
                <td>{{ $totalCurrentTax }}</td>
            </tr>
            <tr>
                <td>Total Deferred Tax</td>
                <td>{{ $totalDeferredTax }}</td>
            </tr>
            <tr>
                <td>Total Tax</td>
                <td>{{ $totalTax }}</td>
            </tr>
            <tr>
                <td>Net Profit After Tax</td>
                <td>{{ $netProfitAfterTax }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th colspan="5">Current Tax Entries</th>
            </tr>
            <tr>
                <th>Description</th>
                <th>Party</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($currentTaxEntries as $entry)
                <tr>
                    <td>{{ $entry->description }}</td>
                    <td>{{ optional($entry->client)->name ?? optional($entry->staff)->name ?? 'Organization-wide' }}</td>
                    <td>{{ $entry->amount }}</td>
                    <td>{{ $entry->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $entry->notes }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No current tax entries found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th colspan="5">Deferred Tax Entries</th>
            </tr>
            <tr>
                <th>Description</th>
                <th>Party</th>
                <th>Amount</th>
                <th>Date</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @forelse($deferredTaxEntries as $entry)
                <tr>
                    <td>{{ $entry->description }}</td>
                    <td>{{ optional($entry->client)->name ?? optional($entry->staff)->name ?? 'Organization-wide' }}</td>
                    <td>{{ $entry->amount }}</td>
                    <td>{{ $entry->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $entry->notes }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No deferred tax entries found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('pdfs.partials.report_footer')
</body>
</html>
