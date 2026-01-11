<!DOCTYPE html>
<html>
<head>
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        body { font-family: sans-serif; color: #111827; }
        h1, h2, h3 { margin: 0 0 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; }
        th { background-color: #f3f4f6; text-align: left; }
        .muted { color: #6b7280; font-size: 12px; }
        .summary { width: 100%; margin-top: 16px; }
        .summary td { border: none; padding: 4px 0; }
        .right { text-align: right; }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    <h1>Invoice</h1>
    <p class="muted">Invoice No: {{ $invoice->invoice_no }}</p>

    <table>
        <thead>
            <tr>
                <th colspan="2">Invoice Details</th>
                <th colspan="2">Client Details</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Invoice Date</td>
                <td>{{ $invoice->invoice_date->format('Y-m-d') }}</td>
                <td>Client Name</td>
                <td>{{ $invoice->client_name }}</td>
            </tr>
            <tr>
                <td>Service</td>
                <td>{{ $invoice->service_category }} - {{ $invoice->service_type }}</td>
                <td>Organization</td>
                <td>{{ $invoice->organization_name ?? '—' }}</td>
            </tr>
            <tr>
                <td>Payment Status</td>
                <td>{{ ucfirst($invoice->payment_status) }}</td>
                <td>Mobile</td>
                <td>{{ $invoice->client_mobile ?? '—' }}</td>
            </tr>
            <tr>
                <td>Payment Method</td>
                <td>{{ $invoice->payment_method ?? '—' }}</td>
                <td>Email</td>
                <td>{{ $invoice->client_email ?? '—' }}</td>
            </tr>
        </tbody>
    </table>

    @if(!empty($invoice->description))
        <h2>Description</h2>
        <p>{{ $invoice->description }}</p>
    @endif

    <h2>Invoice Items</h2>
    <table>
        <thead>
            <tr>
                <th>SL</th>
                <th>Service Description</th>
                <th class="right">Qty</th>
                <th class="right">Unit Price</th>
                <th class="right">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $item)
                <tr>
                    <td>{{ $item->sl }}</td>
                    <td>{{ $item->service_description }}</td>
                    <td class="right">{{ number_format($item->quantity, 2) }}</td>
                    <td class="right">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="right">No items found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td>Subtotal</td>
            <td class="right">{{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td>Discount</td>
            <td class="right">{{ number_format($invoice->discount_amount, 2) }}</td>
        </tr>
        <tr>
            <td>VAT ({{ number_format($invoice->vat_rate, 2) }}%)</td>
            <td class="right">{{ number_format($invoice->vat_amount, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Total Amount</strong></td>
            <td class="right"><strong>{{ number_format($invoice->total_amount, 2) }}</strong></td>
        </tr>
        <tr>
            <td>Paid Amount</td>
            <td class="right">{{ number_format($invoice->paid_amount, 2) }}</td>
        </tr>
        <tr>
            <td>Due Amount</td>
            <td class="right">{{ number_format($invoice->due_amount, 2) }}</td>
        </tr>
    </table>

    <h2>Company Contact</h2>
    <p><strong>Phone:</strong> {{ $invoice->company_phone ?? '—' }}</p>
    <p><strong>Email:</strong> {{ $invoice->company_email ?? '—' }}</p>
    <p><strong>Address:</strong> {{ $invoice->company_address ?? '—' }}</p>

    @include('pdfs.partials.report_footer')
</body>
</html>
