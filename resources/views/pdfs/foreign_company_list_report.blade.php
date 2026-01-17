<!DOCTYPE html>
<html>
<head>
    <title>Foreign Company Report</title>
    <style>
        body { font-family: sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; font-size: 12px; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    @include('pdfs.partials.company_header')

    <h1>Foreign Company Report</h1>
    <p>All foreign companies</p>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Country</th>
                <th>Job Categories</th>
                <th>Owner</th>
                <th>Owner Phone</th>
                <th>Contact Person</th>
                <th>Contact Phone</th>
                <th>Per Client Fee</th>
                <th>Office Address</th>
            </tr>
        </thead>
        <tbody>
            @forelse($companies as $company)
                <tr>
                    <td>{{ $company->name }}</td>
                    <td>{{ $company->country }}</td>
                    <td>{{ $company->job_categories }}</td>
                    <td>{{ $company->owner_name }}</td>
                    <td>{{ $company->owner_phone }}</td>
                    <td>{{ $company->contact_person_name }}</td>
                    <td>{{ $company->contact_person_phone }}</td>
                    <td>{{ $company->per_client_fee }}</td>
                    <td>{{ $company->office_address }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center;">No companies found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @include('pdfs.partials.report_footer')
</body>
</html>
