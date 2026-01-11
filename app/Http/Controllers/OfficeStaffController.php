<?php

namespace App\Http\Controllers;

use App\Models\AccountingPeriod;
use App\Models\OfficeStaff;
use App\Models\OperatingExpense;
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

    public function show(Request $request, OfficeStaff $officeStaff)
    {
        $period = AccountingPeriod::where('status', 'active')
            ->orWhere('status', 'draft')
            ->latest()
            ->first();

        if (! $period) {
            $period = AccountingPeriod::create([
                'name' => now()->format('F Y'),
                'type' => 'monthly',
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth(),
                'status' => 'draft',
            ]);
        }

        $monthParam = $request->query('month');
        if ($monthParam) {
            try {
                $monthDate = \Carbon\Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
            } catch (\Exception $e) {
                $monthDate = now()->startOfMonth();
            }
        } else {
            $monthDate = now()->startOfMonth();
        }

        $startDate = $monthDate->copy()->startOfMonth();
        $endDate = $monthDate->copy()->endOfMonth();

        $salarySubcategories = [
            'Salaries & Wages',
            'Salaries and Wages',
            'Salaries And Wages',
            'Salary & Wages',
            'Salary and Wages',
        ];

        $entries = OperatingExpense::where('accounting_period_id', $period->id)
            ->where('category', 'employee_manpower')
            ->where('staff_id', $officeStaff->id)
            ->whereIn('subcategory', $salarySubcategories)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();

        $salaryTotal = (float) $entries->sum('salary_amount');
        $bonusTotal = (float) $entries->sum('bonus_amount');
        $paidTotal = (float) $entries->sum('paid_amount');
        $dueTotal = (float) $entries->sum('due_amount');

        return Inertia::render('OfficeStaff/Show', [
            'staff' => $officeStaff->only([
                'id',
                'name',
                'designation',
                'email',
                'mobile',
                'status',
            ]),
            'period' => [
                'id' => $period->id,
                'name' => $period->name,
            ],
            'month' => [
                'value' => $monthDate->format('Y-m'),
                'label' => $monthDate->format('F Y'),
            ],
            'summary' => [
                'salary_total' => $salaryTotal,
                'bonus_total' => $bonusTotal,
                'paid_total' => $paidTotal,
                'due_total' => $dueTotal,
            ],
            'entries' => $entries->map(fn ($entry) => [
                'id' => $entry->id,
                'subcategory' => $entry->subcategory,
                'description' => $entry->description,
                'salary_amount' => (float) $entry->salary_amount,
                'bonus_amount' => (float) $entry->bonus_amount,
                'paid_amount' => (float) $entry->paid_amount,
                'due_amount' => (float) $entry->due_amount,
                'created_at' => $entry->created_at->format('Y-m-d'),
            ]),
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
