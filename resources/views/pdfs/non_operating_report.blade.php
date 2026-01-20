<!DOCTYPE html>
<html>
<head>
    <title>Non-Operating Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    <h1>Non-Operating Report</h1>
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
                <td>Total Income</td>
                <td>{{ $totalIncome }}</td>
            </tr>
            <tr>
                <td>Total Expense Tax</td>
                <td>{{ $totalExpenseTax }}</td>
            </tr>
            <tr>
                <td>Total Expenses (with Tax)</td>
                <td>{{ $totalExpenses }}</td>
            </tr>
            <tr>
                <td>Net Profit/Loss</td>
                <td>{{ $netNonOperating }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th colspan="5">Income Entries</th>
            </tr>
            <tr>
                <th>Category</th>
                <th>Client</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incomeEntries as $entry)
                <tr>
                    <td>{{ $entry->category }}</td>
                    <td>{{ optional($entry->client)->name ?? 'N/A' }}</td>
                    <td>{{ $entry->description }}</td>
                    <td>{{ $entry->amount }}</td>
                    <td>{{ $entry->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No income entries found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th colspan="7">Expense Entries</th>
            </tr>
            <tr>
                <th>Category</th>
                <th>Client</th>
                <th>Description</th>
                <th>Amount</th>
                <th>Tax Rate</th>
                <th>Tax Amount</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($expenseEntries as $entry)
                <tr>
                    <td>{{ $entry->category }}</td>
                    <td>{{ optional($entry->client)->name ?? 'N/A' }}</td>
                    <td>{{ $entry->description }}</td>
                    <td>{{ $entry->amount }}</td>
                    <td>{{ $entry->tax_rate }}%</td>
                    <td>{{ $entry->tax_amount }}</td>
                    <td>{{ $entry->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">No expense entries found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('pdfs.partials.report_footer')
</body>
</html>
