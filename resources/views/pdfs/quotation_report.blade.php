<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quotation {{ $quotation->quotation_no }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 12px; line-height: 1.6; }
        h1 { font-size: 22px; margin: 0; }
        h2 { font-size: 14px; margin: 0 0 8px; text-transform: uppercase; letter-spacing: 0.04em; color: #1f2937; }
        .muted { color: #6b7280; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid #e5e7eb; padding-bottom: 12px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 999px; background: #e0f2fe; color: #0369a1; font-weight: 600; font-size: 10px; }
        .section { margin-top: 18px; }
        .info-grid { width: 100%; border-collapse: collapse; }
        .info-grid td { padding: 6px 0; vertical-align: top; }
        .info-grid .label { width: 160px; color: #334155; font-weight: 600; }
        .card { border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px 14px; background: #f8fafc; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.items th, table.items td { border: 1px solid #e5e7eb; padding: 8px; }
        table.items th { background: #f1f5f9; text-align: left; font-weight: 600; color: #0f172a; }
        table.items td { background: #ffffff; }
        .right { text-align: right; }
        .terms { white-space: pre-line; }
        .two-col { width: 100%; border-collapse: collapse; margin-top: 6px; }
        .two-col td { width: 50%; vertical-align: top; }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    <div class="header">
        <div>
            <h1>Quotation</h1>
            <div class="muted">Prepared for client service proposal</div>
        </div>
        <div class="right">
            <div class="badge">{{ $quotation->quotation_no }}</div>
            <div class="muted" style="margin-top: 6px;">
                Date: {{ $quotation->quotation_date?->format('Y-m-d') }}<br>
                Validity: {{ $quotation->valid_until?->format('Y-m-d') }}
            </div>
        </div>
    </div>

    <div class="section">
        <table class="two-col">
            <tr>
                <td>
                    <div class="card">
                        <h2>Client Info</h2>
                        <table class="info-grid">
                            <tr>
                                <td class="label">Client Name</td>
                                <td>{{ $quotation->client_name }}</td>
                            </tr>
                            <tr>
                                <td class="label">Organization</td>
                                <td>{{ $quotation->organization_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Mobile</td>
                                <td>{{ $quotation->client_mobile ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Email</td>
                                <td>{{ $quotation->client_email ?? '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td>
                    <div class="card">
                        <h2>Service Info</h2>
                        <table class="info-grid">
                            <tr>
                                <td class="label">Service</td>
                                <td>{{ ucwords(str_replace('_', ' ', $quotation->service_category)) }}</td>
                            </tr>
                            <tr>
                                <td class="label">Service Type</td>
                                <td>{{ $quotation->service_type }}</td>
                            </tr>
                            <tr>
                                <td class="label">Quotation Maker</td>
                                <td>{{ $quotation->quotationMaker?->name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="label">Created By</td>
                                <td>{{ $quotation->creator?->name ?? '—' }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="section card">
        <h2>Description</h2>
        <div class="terms">{{ $quotation->description }}</div>
    </div>

    <div class="section">
        <h2>Items</h2>
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 40px;">SL</th>
                    <th>Service Description</th>
                    <th class="right" style="width: 70px;">Qty</th>
                    <th class="right" style="width: 90px;">Unit Price</th>
                    <th class="right" style="width: 90px;">Discount</th>
                    <th class="right" style="width: 70px;">VAT %</th>
                    <th class="right" style="width: 90px;">VAT Amt</th>
                    <th class="right" style="width: 110px;">Line Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($quotation->items as $item)
                    <tr>
                        <td>{{ $item->sl }}</td>
                        <td>{{ $item->service_description }}</td>
                        <td class="right">{{ number_format($item->quantity ?? 1, 2) }}</td>
                        <td class="right">৳{{ number_format($item->unit_price ?? $item->price, 2) }}</td>
                        <td class="right">৳{{ number_format($item->discount_amount ?? 0, 2) }}</td>
                        <td class="right">{{ number_format($item->vat_rate ?? 0, 2) }}%</td>
                        <td class="right">৳{{ number_format($item->vat_amount ?? 0, 2) }}</td>
                        <td class="right">৳{{ number_format($item->line_total ?? 0, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="section card">
        <h2>Payment Summary</h2>
        <table class="info-grid">
            <tr>
                <td class="label">Subtotal</td>
                <td>৳{{ number_format($summary['subtotal'], 2) }}</td>
            </tr>
            <tr>
                <td class="label">Discount Total</td>
                <td>৳{{ number_format($summary['discount_amount'], 2) }}</td>
            </tr>
            <tr>
                <td class="label">VAT Amount</td>
                <td>৳{{ number_format($summary['vat_amount'], 2) }}</td>
            </tr>
            <tr>
                <td class="label">Total Amount</td>
                <td>৳{{ number_format($summary['total_amount'], 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="section card">
        <h2>Terms & Conditions</h2>
        <div class="terms">{{ $quotation->terms_text }}</div>
    </div>

    @include('pdfs.partials.report_footer')
</body>
</html>
