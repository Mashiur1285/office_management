<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\Client;
use App\Models\OfficeStaff;
use App\Models\OperatingExpense;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class OperatingExpensesController extends Controller
{
    public function showCategory($category)
    {
        // Get active period
        $period = AccountingPeriod::where('status', 'active')
            ->orWhere('status', 'draft')
            ->latest()
            ->first();

        if (!$period) {
            $period = AccountingPeriod::create([
                'name' => now()->format('F Y'),
                'type' => 'monthly',
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth(),
                'status' => 'draft',
            ]);
        }

        // Get operating expenses for this category and period
        $entries = OperatingExpense::where('accounting_period_id', $period->id)
            ->where('category', $category)
            ->with('client:id,name,passport_number', 'staff:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate totals
        $totalAmount = $entries->sum('amount');
        $totalVat = $entries->sum('vat_amount');
        $totalTax = $entries->sum('tax_amount');
        $totalWithVatTax = $totalAmount + $totalVat + $totalTax;

        // Group by subcategory and calculate totals
        $expenseBreakdown = $entries->groupBy('subcategory')
            ->map(fn($items) => [
                'amount' => $items->sum('amount'),
                'vat_amount' => $items->sum('vat_amount'),
                'tax_amount' => $items->sum('tax_amount'),
                'total' => $items->sum('amount') + $items->sum('vat_amount') + $items->sum('tax_amount'),
            ]);

        // Get all periods for selector
        $periods = AccountingPeriod::latest()->get();

        // Get all clients for selection dropdown
        $clients = Client::select('id', 'name', 'passport_number')
            ->orderBy('name')
            ->get();

        $officeStaff = OfficeStaff::select('id', 'name')
            ->orderBy('name')
            ->get();

        // Get subcategories for this category
        $subcategories = \App\Models\Subcategory::where('type', 'operating_expenses')
            ->where('category', $category)
            ->orderBy('name')
            ->get();

        // Map category to display name
        $categoryNames = [
            'employee_manpower' => 'Employee & Manpower',
            'administrative' => 'Administrative',
            'selling_marketing' => 'Selling & Marketing',
            'general' => 'General',
        ];

        return Inertia::render('Accounting/OperatingExpenses/Index', [
            'category' => $category,
            'categoryName' => $categoryNames[$category] ?? $category,
            'period' => [
                'id' => $period->id,
                'name' => $period->name,
                'type' => $period->type,
                'status' => $period->status,
            ],
            'periods' => $periods->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'type' => $p->type,
                'status' => $p->status,
            ]),
            'entries' => $entries->map(fn($e) => [
                'id' => $e->id,
                'client_id' => $e->client_id,
                'staff_id' => $e->staff_id,
                'client' => $e->client ? [
                    'id' => $e->client->id,
                    'name' => $e->client->name,
                    'phone_number' => $e->client->passport_number,
                ] : null,
                'staff' => $e->staff ? [
                    'id' => $e->staff->id,
                    'name' => $e->staff->name,
                ] : null,
                'subcategory' => $e->subcategory,
                'description' => $e->description,
                'salary_amount' => (float) ($e->salary_amount ?? 0),
                'bonus_amount' => (float) ($e->bonus_amount ?? 0),
                'paid_amount' => (float) ($e->paid_amount ?? 0),
                'due_amount' => (float) ($e->due_amount ?? 0),
                'amount' => (float) $e->amount,
                'vat_rate' => (float) $e->vat_rate,
                'vat_amount' => (float) $e->vat_amount,
                'tax_rate' => (float) $e->tax_rate,
                'tax_amount' => (float) $e->tax_amount,
                'notes' => $e->notes,
                'created_at' => $e->created_at->format('Y-m-d H:i'),
            ]),
            'totalAmount' => (float) $totalAmount,
            'totalVat' => (float) $totalVat,
            'totalTax' => (float) $totalTax,
            'totalWithVatTax' => (float) $totalWithVatTax,
            'expenseBreakdown' => $expenseBreakdown,
            'clients' => $clients->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'phone_number' => $c->passport_number,
            ]),
            'officeStaff' => $officeStaff->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
            ]),
            'subcategories' => $subcategories->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'vat_rate' => (float) $s->vat_rate,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'accounting_period_id' => 'required|exists:accounting_periods,id',
            'category' => 'required|in:employee_manpower,administrative,selling_marketing,general',
            'client_id' => 'nullable|exists:clients,id',
            'staff_id' => 'nullable|exists:office_staff,id',
            'subcategory' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'salary_amount' => 'nullable|numeric|min:0',
            'bonus_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['vat_rate'] = $validated['vat_rate'] ?? 0;
        $validated['tax_rate'] = $validated['tax_rate'] ?? 0;

        if ($validated['category'] === 'employee_manpower') {
            if (empty($validated['staff_id'])) {
                return redirect()->back()->withErrors([
                    'staff_id' => 'Please select a staff member.'
                ])->withInput();
            }

            $validated['client_id'] = null;

            if ($this->isSalarySubcategory($validated['subcategory'])) {
                $salary = (float) ($validated['salary_amount'] ?? 0);
                $bonus = (float) ($validated['bonus_amount'] ?? 0);
                $paid = (float) ($validated['paid_amount'] ?? 0);
                $validated['due_amount'] = max(0, ($salary + $bonus) - $paid);
                $validated['amount'] = $paid;
                $validated['vat_rate'] = 0;
            } else {
                $validated['salary_amount'] = null;
                $validated['bonus_amount'] = null;
                $validated['paid_amount'] = null;
                $validated['due_amount'] = null;
            }
        } else {
            if (empty($validated['client_id'])) {
                return redirect()->back()->withErrors([
                    'client_id' => 'Please select a client.'
                ])->withInput();
            }
            $validated['staff_id'] = null;
            $validated['salary_amount'] = null;
            $validated['bonus_amount'] = null;
            $validated['paid_amount'] = null;
            $validated['due_amount'] = null;
        }

        $validated['vat_amount'] = ($validated['amount'] * $validated['vat_rate']) / 100;
        $validated['tax_amount'] = ($validated['amount'] * $validated['tax_rate']) / 100;

        OperatingExpense::create($validated);

        return redirect()->back()->with('success', 'Operating expense entry added successfully.');
    }

    public function update(Request $request, OperatingExpense $operatingExpense)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'staff_id' => 'nullable|exists:office_staff,id',
            'subcategory' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'salary_amount' => 'nullable|numeric|min:0',
            'bonus_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['vat_rate'] = $validated['vat_rate'] ?? 0;
        $validated['tax_rate'] = $validated['tax_rate'] ?? 0;

        if ($operatingExpense->category === 'employee_manpower') {
            if (empty($validated['staff_id'])) {
                return redirect()->back()->withErrors([
                    'staff_id' => 'Please select a staff member.'
                ])->withInput();
            }

            $validated['client_id'] = null;

            if ($this->isSalarySubcategory($validated['subcategory'])) {
                $salary = (float) ($validated['salary_amount'] ?? 0);
                $bonus = (float) ($validated['bonus_amount'] ?? 0);
                $paid = (float) ($validated['paid_amount'] ?? 0);
                $validated['due_amount'] = max(0, ($salary + $bonus) - $paid);
                $validated['amount'] = $paid;
                $validated['vat_rate'] = 0;
            } else {
                $validated['salary_amount'] = null;
                $validated['bonus_amount'] = null;
                $validated['paid_amount'] = null;
                $validated['due_amount'] = null;
            }
        } else {
            if (empty($validated['client_id'])) {
                return redirect()->back()->withErrors([
                    'client_id' => 'Please select a client.'
                ])->withInput();
            }
            $validated['staff_id'] = null;
            $validated['salary_amount'] = null;
            $validated['bonus_amount'] = null;
            $validated['paid_amount'] = null;
            $validated['due_amount'] = null;
        }

        $validated['vat_amount'] = ($validated['amount'] * $validated['vat_rate']) / 100;
        $validated['tax_amount'] = ($validated['amount'] * $validated['tax_rate']) / 100;

        $operatingExpense->update($validated);

        return redirect()->back()->with('success', 'Operating expense entry updated successfully.');
    }

    private function isSalarySubcategory(string $subcategory): bool
    {
        $normalized = strtolower(str_replace(['&', '  '], ['and', ' '], trim($subcategory)));
        return $normalized === 'salaries and wages';
    }

    public function destroy(OperatingExpense $operatingExpense)
    {
        $operatingExpense->delete();

        return redirect()->back()->with('success', 'Operating expense entry deleted successfully.');
    }

    public function report(Request $request, $category)
    {
        $period = AccountingPeriod::where('status', 'active')
            ->orWhere('status', 'draft')
            ->latest()
            ->first();

        $entries = OperatingExpense::where('accounting_period_id', $period->id)
            ->where('category', $category)
            ->with('client:id,name,passport_number', 'staff:id,name')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($request->query('type') === 'pdf') {
            $categoryNames = [
                'employee_manpower' => 'Employee & Manpower',
                'administrative' => 'Administrative',
                'selling_marketing' => 'Selling & Marketing',
                'general' => 'General',
            ];

            $totalAmount = $entries->sum('amount');
            $totalVat = $entries->sum('vat_amount');
            $totalTax = $entries->sum('tax_amount');
            $totalWithVatTax = $totalAmount + $totalVat + $totalTax;

            $fileName = "operating-expenses-report-{$category}-" . now()->format('Y-m-d') . '.pdf';

            return Pdf::loadView('pdfs.operating_expenses_report', [
                'period' => $period,
                'category' => $category,
                'categoryName' => $categoryNames[$category] ?? $category,
                'entries' => $entries,
                'totalAmount' => $totalAmount,
                'totalVat' => $totalVat,
                'totalTax' => $totalTax,
                'totalWithVatTax' => $totalWithVatTax,
            ])->download($fileName);
        }

        $handle = fopen('php://memory', 'w');
        
        fputcsv($handle, ['Subcategory', 'Client/Staff', 'Description', 'Amount', 'VAT Rate', 'VAT Amount', 'Tax Rate', 'Tax Amount', 'Total', 'Date']);

        foreach ($entries as $entry) {
            fputcsv($handle, [
                $entry->subcategory,
                $entry->staff->name ?? $entry->client->name ?? 'N/A',
                $entry->description,
                $entry->amount,
                $entry->vat_rate,
                $entry->vat_amount,
                $entry->tax_rate,
                $entry->tax_amount,
                $entry->amount + $entry->vat_amount + $entry->tax_amount,
                $entry->created_at->format('Y-m-d H:i'),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $fileName = "operating-expenses-report-{$category}-" . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
