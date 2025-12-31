<?php

namespace App\Http\Controllers;

use App\Models\OfficeStaff;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OfficeStaffController extends Controller
{
    public function index()
    {
        $staff = OfficeStaff::orderBy('name')->get();

        return Inertia::render('OfficeStaff/Index', [
            'staff' => $staff,
        ]);
    }

    public function create()
    {
        return Inertia::render('OfficeStaff/Create', [
            'staffMember' => null,
            'mode' => 'create',
        ]);
    }

    public function edit(OfficeStaff $officeStaff)
    {
        return Inertia::render('OfficeStaff/Create', [
            'staffMember' => $officeStaff->only([
                'id',
                'name',
                'designation',
                'email',
                'mobile',
                'nid_number',
                'address',
                'joining_date',
                'status',
                'notes',
            ]),
            'mode' => 'edit',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'nid_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'joining_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);

        OfficeStaff::create($data);

        return redirect()->route('office-staff.index')->with('success', 'Office staff member added successfully.');
    }

    public function update(Request $request, OfficeStaff $officeStaff)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'nid_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'joining_date' => ['nullable', 'date'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ]);

        $officeStaff->update($data);

        return redirect()->route('office-staff.index')->with('success', 'Office staff member updated successfully.');
    }
}
