<!DOCTYPE html>
<html>
<head>
    <title>Agent Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; font-size: 12px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    <h1>Agent Report</h1>
    <p>All agents</p>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Mobile</th>
                <th>District</th>
                <th>Services</th>
                <th>Clients</th>
            </tr>
        </thead>
        <tbody>
            @forelse($agents as $agent)
                <tr>
                    <td>{{ $agent->name }}</td>
                    <td>{{ $agent->mobile }}</td>
                    <td>{{ $agent->district }}</td>
                    <td>{{ implode(', ', $agent->services ?? []) }}</td>
                    <td>{{ $agent->clients_count }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No agents found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('pdfs.partials.report_footer')
</body>
</html>
