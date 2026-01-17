<!DOCTYPE html>
<html>
<head>
    <title>Client Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; font-size: 12px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    <h1>Client Report</h1>
    <p>All clients</p>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>NID</th>
                <th>Passport</th>
                <th>Job Sector</th>
                <th>Agent</th>
                <th>Status</th>
                <th>BD Company</th>
                <th>Foreign Company</th>
                <th>Due Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($clients as $client)
                <tr>
                    <td>{{ $client['name'] }}</td>
                    <td>{{ $client['nid_number'] }}</td>
                    <td>{{ $client['passport_number'] }}</td>
                    <td>{{ $client['job_sector'] }}</td>
                    <td>{{ $client['agent_name'] }}</td>
                    <td>{{ $client['status'] }}</td>
                    <td>{{ $client['bd_company'] }}</td>
                    <td>{{ $client['foreign_company'] }}</td>
                    <td>{{ $client['current_due'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">No clients found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('pdfs.partials.report_footer')
</body>
</html>
