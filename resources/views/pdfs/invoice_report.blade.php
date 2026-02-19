<!DOCTYPE html>
<html>
<head>
    <title>Invoice {{ $invoice->invoice_no }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111827; font-size: 11px; margin: 0; padding: 15px; }
        h1 { margin: 0 0 4px; font-size: 18px; }
        h2 { margin: 8px 0 4px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #e5e7eb; padding: 5px 6px; font-size: 10px; }
        th { background-color: #f3f4f6; text-align: left; font-size: 10px; }
        .muted { color: #6b7280; font-size: 10px; }
        .summary { width: 50%; margin-top: 8px; margin-left: auto; }
        .summary td { border: none; padding: 2px 0; font-size: 10px; }
        .right { text-align: right; }
        .terms { white-space: pre-line; font-size: 9px; line-height: 1.3; }
        .items-table th, .items-table td { padding: 4px 5px; font-size: 9px; }
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
        <p style="margin: 2px 0; font-size: 10px;">{{ $invoice->description }}</p>
    @endif

    <h2>Invoice Items</h2>
    <table class="items-table">
        <thead>
            <tr>
                <th>SL</th>
                <th>Service Description</th>
                <th class="right">Qty</th>
                <th class="right">Unit Price</th>
                <th class="right">Discount</th>
                <th class="right">VAT %</th>
                <th class="right">VAT Amt</th>
                <th class="right">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $item)
                <tr>
                    <td>{{ $item->sl }}</td>
                    <td>{{ $item->service_description }}</td>
                    <td class="right">{{ number_format($item->quantity, 2) }}</td>
                    <td class="right">Tk{{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">Tk{{ number_format($item->discount_amount ?? 0, 2) }}</td>
                    <td class="right">{{ number_format($item->vat_rate ?? 0, 2) }}%</td>
                    <td class="right">Tk{{ number_format($item->vat_amount ?? 0, 2) }}</td>
                    <td class="right">Tk{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="right">No items found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="summary">
        <tr>
            <td>Subtotal</td>
            <td class="right">Tk{{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td>Discount</td>
            <td class="right">Tk{{ number_format($invoice->discount_amount, 2) }}</td>
        </tr>
        <tr>
            <td>VAT Amount</td>
            <td class="right">Tk{{ number_format($invoice->vat_amount, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Total Amount</strong></td>
            <td class="right"><strong>Tk{{ number_format($invoice->total_amount, 2) }}</strong></td>
        </tr>
        <tr>
            <td>Paid Amount</td>
            <td class="right">Tk{{ number_format($invoice->paid_amount, 2) }}</td>
        </tr>
        <tr>
            <td>Due Amount</td>
            <td class="right">Tk{{ number_format($invoice->due_amount, 2) }}</td>
        </tr>
    </table>

    <h2>Terms & Conditions</h2>
    <div class="terms">{{ $invoice->terms_text ?: $defaultTerms }}</div>

    @include('pdfs.partials.report_footer')
</body>
</html>
