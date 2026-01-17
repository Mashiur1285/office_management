<!DOCTYPE html>
<html>
<head>
    <title>Office Staff Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; font-size: 12px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    <h1>Office Staff Report</h1>
    <p>All staff members</p>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Designation</th>
                <th>Email</th>
                <th>Mobile</th>
                <th>Joining Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($staff as $member)
                <tr>
                    <td>{{ $member->name }}</td>
                    <td>{{ $member->designation }}</td>
                    <td>{{ $member->email }}</td>
                    <td>{{ $member->mobile }}</td>
                    <td>{{ $member->joining_date }}</td>
                    <td>{{ $member->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">No staff members found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('pdfs.partials.report_footer')
</body>
</html>
