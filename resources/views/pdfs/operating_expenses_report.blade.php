<!DOCTYPE html>
<html>
<head>
    <title>Operating Expenses Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    <h1>Operating Expenses Report - {{ $categoryName }}</h1>
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
                <td>Total Amount</td>
                <td>{{ $totalAmount }}</td>
            </tr>
            <tr>
                <td>Total VAT</td>
                <td>{{ $totalVat }}</td>
            </tr>
            <tr>
                <td>Total Tax</td>
                <td>{{ $totalTax }}</td>
            </tr>
            <tr>
                <td>Total with VAT &amp; Tax</td>
                <td>{{ $totalWithVatTax }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>Subcategory</th>
                <th>Client/Staff</th>
                <th>Description</th>
                <th>Amount</th>
                <th>VAT Rate</th>
                <th>VAT Amount</th>
                <th>Tax Rate</th>
                <th>Tax Amount</th>
                <th>Total</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $entry)
                <tr>
                    <td>{{ $entry->subcategory }}</td>
                    <td>{{ optional($entry->staff)->name ?? optional($entry->client)->name ?? 'N/A' }}</td>
                    <td>{{ $entry->description }}</td>
                    <td>{{ $entry->amount }}</td>
                    <td>{{ $entry->vat_rate }}%</td>
                    <td>{{ $entry->vat_amount }}</td>
                    <td>{{ $entry->tax_rate }}%</td>
                    <td>{{ $entry->tax_amount }}</td>
                    <td>{{ $entry->amount + $entry->vat_amount + $entry->tax_amount }}</td>
                    <td>{{ $entry->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center;">No entries found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('pdfs.partials.report_footer')
</body>
</html>
