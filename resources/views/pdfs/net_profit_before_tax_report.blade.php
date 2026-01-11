<!DOCTYPE html>
<html>
<head>
    <title>Net Profit Before Tax Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    <h1>Net Profit Before Tax Report</h1>
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
                <td>Gross Profit</td>
                <td>{{ $grossProfit }}</td>
            </tr>
            <tr>
                <td>Total Operating Expenses</td>
                <td>{{ $totalOperatingExpenses }}</td>
            </tr>
            <tr>
                <td>Operating Profit</td>
                <td>{{ $operatingProfit }}</td>
            </tr>
            <tr>
                <td>Net Non-Operating</td>
                <td>{{ $netNonOperating }}</td>
            </tr>
            <tr>
                <td>Net Profit Before Tax</td>
                <td>{{ $netProfitBeforeTax }}</td>
            </tr>
        </tbody>
    </table>

    @include('pdfs.partials.report_footer')
</body>
</html>
