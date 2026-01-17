<!DOCTYPE html>
<html>
<head>
    <title>Quotation Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; font-size: 12px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    <h1>Quotation Report</h1>
    <p>All quotations</p>

    <table>
        <thead>
            <tr>
                <th>Quotation No</th>
                <th>Date</th>
                <th>Client</th>
                <th>Service Category</th>
                <th>Service Type</th>
                <th>Maker</th>
                <th>Created By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($quotations as $quotation)
                <tr>
                    <td>{{ $quotation->quotation_no }}</td>
                    <td>{{ $quotation->quotation_date?->format('Y-m-d') }}</td>
                    <td>{{ $quotation->client_name }}</td>
                    <td>{{ $quotation->service_category }}</td>
                    <td>{{ $quotation->service_type }}</td>
                    <td>{{ $quotation->quotationMaker?->name }}</td>
                    <td>{{ $quotation->creator?->name }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">No quotations found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('pdfs.partials.report_footer')
</body>
</html>
