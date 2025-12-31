<?php

namespace App\Http\Controllers;

use App\Models\ForeignCompany;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

        ForeignCompany::create($data);

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
}
