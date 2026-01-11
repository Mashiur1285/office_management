<!DOCTYPE html>
<html>
<head>
    <title>Cost of Sales Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    <h1>Cost of Sales Report - {{ $categoryName }}</h1>
    <p>Period: {{ $period->name }}</p>
    <table>
        <thead>
            <tr>
                <th>Subcategory</th>
                <th>Client</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $entry)
                <tr>
                    <td>{{ $entry->subcategory }}</td>
                    <td>{{ optional($entry->client)->name ?? 'N/A' }}</td>
                    <td>{{ $entry->description }}</td>
                    <td>{{ $entry->amount }}</td>
                    <td>{{ $entry->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No entries found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    @include('pdfs.partials.report_footer')
</body>
</html>
