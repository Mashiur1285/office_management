<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\Client;
use App\Models\NonOperatingEntry;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class NonOperatingController extends Controller
{
    public function index()
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

        // Get all periods for selector
        $periods = AccountingPeriod::latest()->get();

        // Get all clients for selection dropdown
        $clients = Client::select('id', 'name', 'passport_number')
            ->orderBy('name')
            ->get();

        // Get non-operating entries for this period
        $entries = NonOperatingEntry::where('accounting_period_id', $period->id)
            ->with('client:id,name,mobile')
            ->orderBy('type', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Separate income and expenses
        $incomeEntries = $entries->where('type', 'income');
        $expenseEntries = $entries->where('type', 'expense');

        // Calculate totals
        $totalIncome = $incomeEntries->sum('amount');
        $totalExpenseTax = $expenseEntries->sum('tax_amount');
        $totalExpenses = $expenseEntries->sum(fn($entry) => $entry->amount + $entry->tax_amount);
        $netNonOperating = $totalIncome - $totalExpenses;

        // Group by category for breakdown
        $incomeBreakdown = $incomeEntries->groupBy('category')
            ->map(fn($items) => $items->sum('amount'));

        $expenseBreakdown = $expenseEntries->groupBy('category')
            ->map(fn($items) => $items->sum(fn($entry) => $entry->amount + $entry->tax_amount));

        $subcategories = Subcategory::where('type', 'non_operating')
            ->orderBy('name')
            ->get();

        return Inertia::render('Accounting/NonOperating/Index', [
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
            'incomeEntries' => $incomeEntries->map(fn($e) => [
                'id' => $e->id,
                'client_id' => $e->client_id,
                'client' => $e->client ? [
                    'id' => $e->client->id,
                    'name' => $e->client->name,
                    'phone_number' => $e->client->passport_number,
                ] : null,
                'category' => $e->category,
                'description' => $e->description,
                'amount' => (float) $e->amount,
                'tax_rate' => (float) $e->tax_rate,
                'tax_amount' => (float) $e->tax_amount,
                'notes' => $e->notes,
                'created_at' => $e->created_at->format('Y-m-d H:i'),
            ])->values(),
            'expenseEntries' => $expenseEntries->map(fn($e) => [
                'id' => $e->id,
                'client_id' => $e->client_id,
                'client' => $e->client ? [
                    'id' => $e->client->id,
                    'name' => $e->client->name,
                    'phone_number' => $e->client->passport_number,
                ] : null,
                'category' => $e->category,
                'description' => $e->description,
                'amount' => (float) $e->amount,
                'tax_rate' => (float) $e->tax_rate,
                'tax_amount' => (float) $e->tax_amount,
                'notes' => $e->notes,
                'created_at' => $e->created_at->format('Y-m-d H:i'),
            ])->values(),
            'totalIncome' => (float) $totalIncome,
            'totalExpenseTax' => (float) $totalExpenseTax,
            'totalExpenses' => (float) $totalExpenses,
            'netNonOperating' => (float) $netNonOperating,
            'incomeBreakdown' => $incomeBreakdown,
            'expenseBreakdown' => $expenseBreakdown,
            'clients' => $clients->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'phone_number' => $c->passport_number,
            ]),
            'subcategories' => $subcategories->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'category' => $s->category,
                'vat_rate' => (float) $s->vat_rate,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'accounting_period_id' => 'required|exists:accounting_periods,id',
            'client_id' => 'required|exists:clients,id',
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['tax_rate'] = $validated['tax_rate'] ?? 0;
        $validated['tax_amount'] = $validated['type'] === 'expense'
            ? ($validated['amount'] * $validated['tax_rate']) / 100
            : 0;

        NonOperatingEntry::create($validated);

        return redirect()->back()->with('success', 'Non-operating entry added successfully.');
    }

    public function update(Request $request, NonOperatingEntry $nonOperatingEntry)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['tax_rate'] = $validated['tax_rate'] ?? 0;
        $validated['tax_amount'] = $nonOperatingEntry->type === 'expense'
            ? ($validated['amount'] * $validated['tax_rate']) / 100
            : 0;

        $nonOperatingEntry->update($validated);

        return redirect()->back()->with('success', 'Non-operating entry updated successfully.');
    }

    public function destroy(NonOperatingEntry $nonOperatingEntry)
    {
        $nonOperatingEntry->delete();

        return redirect()->back()->with('success', 'Non-operating entry deleted successfully.');
    }

    public function report(Request $request)
    {
        $period = AccountingPeriod::where('status', 'active')
            ->orWhere('status', 'draft')
            ->latest()
            ->first();

        $entries = NonOperatingEntry::where('accounting_period_id', $period->id)
            ->with('client:id,name,mobile')
            ->orderBy('type', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $incomeEntries = $entries->where('type', 'income');
        $expenseEntries = $entries->where('type', 'expense');
        $totalIncome = $incomeEntries->sum('amount');
        $totalExpenseTax = $expenseEntries->sum('tax_amount');
        $totalExpenses = $expenseEntries->sum(fn($entry) => $entry->amount + $entry->tax_amount);
        $netNonOperating = $totalIncome - $totalExpenses;

        if ($request->query('type') === 'pdf') {
            $fileName = "non-operating-report-" . now()->format('Y-m-d') . '.pdf';

            return Pdf::loadView('pdfs.non_operating_report', [
                'period' => $period,
                'incomeEntries' => $incomeEntries,
                'expenseEntries' => $expenseEntries,
                'totalIncome' => $totalIncome,
                'totalExpenseTax' => $totalExpenseTax,
                'totalExpenses' => $totalExpenses,
                'netNonOperating' => $netNonOperating,
            ])->download($fileName);
        }

        $handle = fopen('php://memory', 'w');

        // Summary
        fputcsv($handle, ['Non-Operating Summary']);
        fputcsv($handle, ['Metric', 'Amount']);
        fputcsv($handle, ['Total Income', $totalIncome]);
        fputcsv($handle, ['Total Expense Tax', $totalExpenseTax]);
        fputcsv($handle, ['Total Expenses (with Tax)', $totalExpenses]);
        fputcsv($handle, ['Net Profit/Loss', $netNonOperating]);
        fputcsv($handle, []);

        // Detailed Entries
        fputcsv($handle, ['Detailed Entries']);
        fputcsv($handle, ['Type', 'Subcategory', 'Client', 'Description', 'Amount', 'Tax Rate', 'Tax Amount', 'Total', 'Date']);

        foreach ($incomeEntries as $entry) {
            fputcsv($handle, [
                'Income',
                $entry->category,
                $entry->client->name ?? 'N/A',
                $entry->description,
                $entry->amount,
                $entry->tax_rate,
                $entry->tax_amount,
                $entry->amount + $entry->tax_amount,
                $entry->created_at->format('Y-m-d H:i'),
            ]);
        }

        foreach ($expenseEntries as $entry) {
            fputcsv($handle, [
                'Expense',
                $entry->category,
                $entry->client->name ?? 'N/A',
                $entry->description,
                $entry->amount,
                $entry->tax_rate,
                $entry->tax_amount,
                $entry->amount + $entry->tax_amount,
                $entry->created_at->format('Y-m-d H:i'),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $fileName = "non-operating-report-" . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
