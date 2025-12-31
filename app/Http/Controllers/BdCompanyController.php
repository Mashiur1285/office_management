<?php

namespace App\Http\Controllers;

use App\Models\BdCompany;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
}
