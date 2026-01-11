<!DOCTYPE html>
<html>
<head>
    <title>Gross Profit Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    <h1>Gross Profit Report</h1>
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
                <td>Total Cost of Sales</td>
                <td>{{ $totalCostOfSales }}</td>
            </tr>
            <tr>
                <td>Gross Profit</td>
                <td>{{ $grossProfit }}</td>
            </tr>
            <tr>
                <td>Gross Margin</td>
                <td>{{ number_format($grossMarginPercentage, 2) }}%</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Income</th>
                <th>Costs</th>
                <th>Profit</th>
                <th>Margin (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $key => $name)
                @php
                    $income = $incomeByCategory[$key] ?? 0;
                    $cost = $costsByCategory[$key] ?? 0;
                    $profit = $income - $cost;
                    $margin = $income > 0 ? (($profit / $income) * 100) : 0;
                @endphp
                <tr>
                    <td>{{ $name }}</td>
                    <td>{{ $income }}</td>
                    <td>{{ $cost }}</td>
                    <td>{{ $profit }}</td>
                    <td>{{ number_format($margin, 2) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @include('pdfs.partials.report_footer')
</body>
</html>
