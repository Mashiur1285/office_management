<!DOCTYPE html>
<html>
<head>
    <title>VAT Summary Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    <h1>VAT Summary Report</h1>
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
                <td>Total VAT Collected</td>
                <td>{{ $totalVat }}</td>
            </tr>
            <tr>
                <td>Total Payments Made</td>
                <td>{{ $totalVatPayments }}</td>
            </tr>
            <tr>
                <td>Remaining Balance</td>
                <td>{{ $vatBalance }}</td>
            </tr>
        </tbody>
    </table>

    <table>
        <thead>
            <tr>
                <th colspan="4">VAT Payment History</th>
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
                <th colspan="3">Detailed VAT Breakdown</th>
            </tr>
            <tr>
                <th>Category</th>
                <th>Subcategory</th>
                <th>VAT Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($vatByCategory as $category => $subcategories)
                @foreach($subcategories as $subcategory => $amount)
                    <tr>
                        <td>{{ $category }}</td>
                        <td>{{ $subcategory }}</td>
                        <td>{{ $amount }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="3" style="text-align: center;">No VAT breakdown found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('pdfs.partials.report_footer')
</body>
</html>
