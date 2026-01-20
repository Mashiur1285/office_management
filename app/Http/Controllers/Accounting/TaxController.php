<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\Client;
use App\Models\OfficeStaff;
use App\Models\TaxEntry;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class TaxController extends Controller
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
        $clients = Client::select('id', 'name', 'mobile')
            ->orderBy('name')
            ->get();

        $officeStaff = OfficeStaff::select('id', 'name')
            ->orderBy('name')
            ->get();

        // Get tax entries for this period
        $entries = TaxEntry::where('accounting_period_id', $period->id)
            ->with('client:id,name,mobile', 'staff:id,name')
            ->orderBy('tax_type', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Separate current and deferred tax
        $currentTaxEntries = $entries->where('tax_type', 'current');
        $deferredTaxEntries = $entries->where('tax_type', 'deferred');

        // Calculate totals
        $totalCurrentTax = $currentTaxEntries->sum('amount');
        $totalDeferredTax = $deferredTaxEntries->sum('amount');
        $totalTax = $totalCurrentTax + $totalDeferredTax;

        // Get net profit before tax for reference
        $period->load(['incomeEntries', 'costOfSales', 'operatingExpenses', 'nonOperatingEntries']);
        $netProfitBeforeTax = $period->net_profit_before_tax;
        $netProfitAfterTax = $netProfitBeforeTax - $totalTax;

        return Inertia::render('Accounting/Tax/Index', [
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
            'currentTaxEntries' => $currentTaxEntries->map(fn($e) => [
                'id' => $e->id,
                'client_id' => $e->client_id,
                'staff_id' => $e->staff_id,
                'client' => $e->client ? [
                    'id' => $e->client->id,
                    'name' => $e->client->name,
                    'phone_number' => $e->client->mobile,
                ] : null,
                'staff' => $e->staff ? [
                    'id' => $e->staff->id,
                    'name' => $e->staff->name,
                ] : null,
                'description' => $e->description,
                'amount' => (float) $e->amount,
                'notes' => $e->notes,
                'created_at' => $e->created_at->format('Y-m-d H:i'),
            ])->values(),
            'deferredTaxEntries' => $deferredTaxEntries->map(fn($e) => [
                'id' => $e->id,
                'client_id' => $e->client_id,
                'staff_id' => $e->staff_id,
                'client' => $e->client ? [
                    'id' => $e->client->id,
                    'name' => $e->client->name,
                    'phone_number' => $e->client->mobile,
                ] : null,
                'staff' => $e->staff ? [
                    'id' => $e->staff->id,
                    'name' => $e->staff->name,
                ] : null,
                'description' => $e->description,
                'amount' => (float) $e->amount,
                'notes' => $e->notes,
                'created_at' => $e->created_at->format('Y-m-d H:i'),
            ])->values(),
            'totalCurrentTax' => (float) $totalCurrentTax,
            'totalDeferredTax' => (float) $totalDeferredTax,
            'totalTax' => (float) $totalTax,
            'netProfitBeforeTax' => (float) $netProfitBeforeTax,
            'netProfitAfterTax' => (float) $netProfitAfterTax,
            'clients' => $clients->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'phone_number' => $c->mobile,
            ]),
            'officeStaff' => $officeStaff->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->name,
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'accounting_period_id' => 'required|exists:accounting_periods,id',
            'client_id' => 'nullable|exists:clients,id',
            'staff_id' => 'nullable|exists:office_staff,id',
            'tax_type' => 'required|in:current,deferred',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if (!empty($validated['staff_id'])) {
            $validated['client_id'] = null;
        } elseif (!empty($validated['client_id'])) {
            $validated['staff_id'] = null;
        }

        TaxEntry::create($validated);

        return redirect()->back()->with('success', 'Tax entry added successfully.');
    }

    public function update(Request $request, TaxEntry $taxEntry)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'staff_id' => 'nullable|exists:office_staff,id',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if (!empty($validated['staff_id'])) {
            $validated['client_id'] = null;
        } elseif (!empty($validated['client_id'])) {
            $validated['staff_id'] = null;
        }

        $taxEntry->update($validated);

        return redirect()->back()->with('success', 'Tax entry updated successfully.');
    }

    public function destroy(TaxEntry $taxEntry)
    {
        $taxEntry->delete();

        return redirect()->back()->with('success', 'Tax entry deleted successfully.');
    }

    public function report(Request $request)
    {
        $period = AccountingPeriod::where('status', 'active')
            ->orWhere('status', 'draft')
            ->latest()
            ->first();

        $entries = TaxEntry::where('accounting_period_id', $period->id)
            ->with('client:id,name,mobile', 'staff:id,name')
            ->orderBy('tax_type', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $currentTaxEntries = $entries->where('tax_type', 'current');
        $deferredTaxEntries = $entries->where('tax_type', 'deferred');
        $totalCurrentTax = $currentTaxEntries->sum('amount');
        $totalDeferredTax = $deferredTaxEntries->sum('amount');
        $totalTax = $totalCurrentTax + $totalDeferredTax;

        $period->load(['incomeEntries', 'costOfSales', 'operatingExpenses', 'nonOperatingEntries']);
        $netProfitBeforeTax = $period->net_profit_before_tax;
        $netProfitAfterTax = $netProfitBeforeTax - $totalTax;

        if ($request->query('type') === 'pdf') {
            $fileName = "tax-management-report-" . now()->format('Y-m-d') . '.pdf';

            return Pdf::loadView('pdfs.tax_report', [
                'period' => $period,
                'currentTaxEntries' => $currentTaxEntries,
                'deferredTaxEntries' => $deferredTaxEntries,
                'totalCurrentTax' => $totalCurrentTax,
                'totalDeferredTax' => $totalDeferredTax,
                'totalTax' => $totalTax,
                'netProfitBeforeTax' => $netProfitBeforeTax,
                'netProfitAfterTax' => $netProfitAfterTax,
            ])->download($fileName);
        }

        $handle = fopen('php://memory', 'w');

        // Summary
        fputcsv($handle, ['Tax Management Summary']);
        fputcsv($handle, ['Metric', 'Amount']);
        fputcsv($handle, ['Net Profit Before Tax', $netProfitBeforeTax]);
        fputcsv($handle, ['Total Current Tax', $totalCurrentTax]);
        fputcsv($handle, ['Total Deferred Tax', $totalDeferredTax]);
        fputcsv($handle, ['Total Tax', $totalTax]);
        fputcsv($handle, ['Net Profit After Tax', $netProfitAfterTax]);
        fputcsv($handle, []);

        // Detailed Entries
        fputcsv($handle, ['Detailed Tax Entries']);
        fputcsv($handle, ['Type', 'Description', 'Party', 'Amount', 'Date']);

        foreach ($currentTaxEntries as $entry) {
            fputcsv($handle, [
                'Current Tax',
                $entry->description,
                $entry->client->name ?? $entry->staff->name ?? 'Organization-wide',
                $entry->amount,
                $entry->created_at->format('Y-m-d H:i'),
            ]);
        }

        foreach ($deferredTaxEntries as $entry) {
            fputcsv($handle, [
                'Deferred Tax',
                $entry->description,
                $entry->client->name ?? $entry->staff->name ?? 'Organization-wide',
                $entry->amount,
                $entry->created_at->format('Y-m-d H:i'),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $fileName = "tax-management-report-" . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
