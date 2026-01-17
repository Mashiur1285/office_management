<?php

namespace App\Http\Controllers;

use App\Models\ForeignCompany;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class ForeignCompanyController extends Controller
{
    public function index()
    {
        $companies = ForeignCompany::query()
            ->latest()
            ->get();

        return Inertia::render('ForeignCompanies/Index', [
            'companies' => $companies,
        ]);
    }

    public function export(Request $request, ?string $type = null)
    {
        $companies = ForeignCompany::query()
            ->latest()
            ->get();

        if ($type === 'pdf') {
            $fileName = 'foreign-companies-report-' . now()->format('Y-m-d') . '.pdf';

            return Pdf::loadView('pdfs.foreign_company_list_report', [
                'companies' => $companies,
            ])->download($fileName);
        }

        $handle = fopen('php://memory', 'w');

        fputcsv($handle, [
            'Name',
            'Country',
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
                $company->country,
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

        $fileName = 'foreign-companies-report-' . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function create()
    {
        return Inertia::render('ForeignCompanies/Create', [
            'company' => null,
            'mode' => 'create',
        ]);
    }

    public function edit(ForeignCompany $foreignCompany)
    {
        return Inertia::render('ForeignCompanies/Create', [
            'company' => $foreignCompany->only([
                'id',
                'name',
                'country',
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
            'country' => ['nullable', 'string', 'max:255'],
            'job_categories' => ['nullable', 'string', 'max:255'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:50'],
            'office_address' => ['nullable', 'string'],
            'contact_person_name' => ['nullable', 'string', 'max:255'],
            'contact_person_phone' => ['nullable', 'string', 'max:50'],
            'per_client_fee' => ['nullable', 'numeric', 'min:0'],
        ]);

        $company = ForeignCompany::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'foreignCompany' => $company->only([
                    'id',
                    'name',
                    'country',
                    'contact_person_phone',
                    'owner_phone',
                    'contact_person_name',
                ]),
            ], 201);
        }

        return redirect()->route('foreign-companies.index')->with('success', 'Foreign company added.');
    }

    public function update(Request $request, ForeignCompany $foreignCompany)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'job_categories' => ['nullable', 'string', 'max:255'],
            'owner_name' => ['nullable', 'string', 'max:255'],
            'owner_phone' => ['nullable', 'string', 'max:50'],
            'office_address' => ['nullable', 'string'],
            'contact_person_name' => ['nullable', 'string', 'max:255'],
            'contact_person_phone' => ['nullable', 'string', 'max:50'],
            'per_client_fee' => ['nullable', 'numeric', 'min:0'],
        ]);

        $foreignCompany->update($data);

        return redirect()->route('foreign-companies.index')->with('success', 'Foreign company updated.');
    }

    public function show(ForeignCompany $foreignCompany)
    {
        $clients = Client::query()
            ->where(function ($query) use ($foreignCompany) {
                $query->where('foreign_company_id', $foreignCompany->id);

                if ($foreignCompany->name) {
                    $query->orWhere(function ($inner) use ($foreignCompany) {
                        $inner->whereNull('foreign_company_id')
                            ->where('foreign_company_name', $foreignCompany->name);
                    });
                }

                if ($foreignCompany->country) {
                    $query->orWhere(function ($inner) use ($foreignCompany) {
                        $inner->whereNull('foreign_company_id')
                            ->where('foreign_company_country', $foreignCompany->country);
                    });
                }
            })
            ->orderBy('name')
            ->get()
            ->map(fn (Client $client) => [
                'id' => $client->id,
                'name' => $client->name,
                'passport_number' => $client->passport_number,
                'nid_number' => $client->nid_number,
            ]);

        return Inertia::render('ForeignCompanies/Show', [
            'company' => $foreignCompany,
            'clients' => $clients,
        ]);
    }

    public function country(string $country)
    {
        $clients = Client::query()
            ->where(function ($query) use ($country) {
                $query->where('foreign_company_country', $country)
                    ->orWhereHas('foreignCompany', function ($foreignQuery) use ($country) {
                        $foreignQuery->where('country', $country);
                    });
            })
            ->orderBy('name')
            ->get()
            ->map(fn (Client $client) => [
                'id' => $client->id,
                'name' => $client->name,
                'passport_number' => $client->passport_number,
                'nid_number' => $client->nid_number,
            ]);

        return Inertia::render('ForeignCompanies/CountryShow', [
            'country' => $country,
            'clients' => $clients,
        ]);
    }
}
