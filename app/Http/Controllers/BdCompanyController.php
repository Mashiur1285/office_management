<?php

namespace App\Http\Controllers;

use App\Models\BdCompany;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class BdCompanyController extends Controller
{
    public function index()
    {
        $companies = BdCompany::query()
            ->latest()
            ->get();

        return Inertia::render('BdCompanies/Index', [
            'companies' => $companies,
        ]);
    }

    public function export(Request $request, ?string $type = null)
    {
        $companies = BdCompany::query()
            ->latest()
            ->get();

        if ($type === 'pdf') {
            $fileName = 'bd-companies-report-' . now()->format('Y-m-d') . '.pdf';

            return Pdf::loadView('pdfs.bd_company_list_report', [
                'companies' => $companies,
            ])->download($fileName);
        }

        $handle = fopen('php://memory', 'w');

        fputcsv($handle, [
            'Name',
            'Job Categories',
            'Owner',
            'Owner Phone',
            'Contact Person',
            'Contact Phone',
            'Per Client Fee',
            'Office Address',
        ]);

        foreach ($companies as $company) {
            fputcsv($handle, [
                $company->name,
                $company->job_categories,
                $company->owner_name,
                $company->owner_phone,
                $company->contact_person_name,
                $company->contact_person_phone,
                $company->per_client_fee,
                $company->office_address,
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $fileName = 'bd-companies-report-' . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function create()
    {
        return Inertia::render('BdCompanies/Create', [
            'company' => null,
            'mode' => 'create',
        ]);
    }

    public function edit(BdCompany $bdCompany)
    {
        return Inertia::render('BdCompanies/Create', [
            'company' => $bdCompany->only([
                'id',
                'name',
                'job_categories',
                'owner_name',
                'owner_phone',
                'office_address',
                'contact_person_name',
                'contact_person_phone',
                'per_client_fee',
            ]),
            'mode' => 'edit',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'job_categories' => ['nullable', 'string', 'max:255'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:50'],
            'office_address' => ['nullable', 'string'],
            'contact_person_name' => ['nullable', 'string', 'max:255'],
            'contact_person_phone' => ['nullable', 'string', 'max:50'],
            'per_client_fee' => ['nullable', 'numeric', 'min:0'],
        ]);

        BdCompany::create($data);

        return redirect()->route('bd-companies.index')->with('success', 'Bangladeshi company added.');
    }

    public function update(Request $request, BdCompany $bdCompany)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'job_categories' => ['nullable', 'string', 'max:255'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:50'],
            'office_address' => ['nullable', 'string'],
            'contact_person_name' => ['nullable', 'string', 'max:255'],
            'contact_person_phone' => ['nullable', 'string', 'max:50'],
            'per_client_fee' => ['nullable', 'numeric', 'min:0'],
        ]);

        $bdCompany->update($data);

        return redirect()->route('bd-companies.index')->with('success', 'Bangladeshi company updated.');
    }

    public function show(BdCompany $bdCompany)
    {
        $clients = Client::query()
            ->where('bd_company_id', $bdCompany->id)
            ->orderBy('name')
            ->get()
            ->map(fn (Client $client) => [
                'id' => $client->id,
                'name' => $client->name,
                'passport_number' => $client->passport_number,
                'nid_number' => $client->nid_number,
            ]);

        return Inertia::render('BdCompanies/Show', [
            'company' => $bdCompany,
            'clients' => $clients,
        ]);
    }
}
