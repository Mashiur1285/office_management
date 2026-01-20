<!DOCTYPE html>
<html>
<head>
    <title>Operating Profit Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    <h1>Operating Profit Report</h1>
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
                <td>Operating Expenses</td>
                <td>{{ $totalOperatingExpenses }}</td>
            </tr>
            <tr>
                <td>Operating Expenses VAT</td>
                <td>{{ $totalOperatingExpensesVat }}</td>
            </tr>
            <tr>
                <td>Operating Expenses Tax</td>
                <td>{{ $totalOperatingExpensesTax }}</td>
            </tr>
            <tr>
                <td>Operating Expenses Total</td>
                <td>{{ $totalOperatingExpensesWithVatTax }}</td>
            </tr>
            <tr>
                <td>Operating Profit</td>
                <td>{{ $operatingProfit }}</td>
            </tr>
            <tr>
                <td>Operating Margin</td>
                <td>{{ number_format($operatingMargin, 2) }}%</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th>Category</th>
                <th>Amount</th>
                <th>VAT</th>
                <th>Tax</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $key => $name)
                @php
                    $amount = $expensesByCategory[$key]['amount'] ?? 0;
                    $vatAmount = $expensesByCategory[$key]['vat_amount'] ?? 0;
                    $taxAmount = $expensesByCategory[$key]['tax_amount'] ?? 0;
                    $total = $amount + $vatAmount + $taxAmount;
                @endphp
                <tr>
                    <td>{{ $name }}</td>
                    <td>{{ $amount }}</td>
                    <td>{{ $vatAmount }}</td>
                    <td>{{ $taxAmount }}</td>
                    <td>{{ $total }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @include('pdfs.partials.report_footer')
</body>
</html>
