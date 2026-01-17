<!DOCTYPE html>
<html>
<head>
    <title>Invoice Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; font-size: 12px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    <h1>Invoice Report</h1>
    <p>All invoices</p>

    <table>
        <thead>
            <tr>
                <th>Invoice No</th>
                <th>Date</th>
                <th>Client</th>
                <th>Service Category</th>
                <th>Service Type</th>
                <th>Total</th>
                <th>Paid</th>
                <th>Due</th>
                <th>Status</th>
                <th>Created By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->invoice_no }}</td>
                    <td>{{ $invoice->invoice_date?->format('Y-m-d') }}</td>
                    <td>{{ $invoice->client_name }}</td>
                    <td>{{ $invoice->service_category }}</td>
                    <td>{{ $invoice->service_type }}</td>
                    <td>{{ $invoice->total_amount }}</td>
                    <td>{{ $invoice->paid_amount }}</td>
                    <td>{{ $invoice->due_amount }}</td>
                    <td>{{ $invoice->payment_status }}</td>
                    <td>{{ $invoice->creator?->name }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" style="text-align: center;">No invoices found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('pdfs.partials.report_footer')
</body>
</html>
