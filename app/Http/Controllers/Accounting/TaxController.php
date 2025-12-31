<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\Client;
use App\Models\TaxEntry;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

        // Get tax entries for this period
        $entries = TaxEntry::where('accounting_period_id', $period->id)
            ->with('client:id,name,mobile')
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
                'client' => $e->client ? [
                    'id' => $e->client->id,
                    'name' => $e->client->name,
                    'phone_number' => $e->client->mobile,
                ] : null,
                'description' => $e->description,
                'amount' => (float) $e->amount,
                'notes' => $e->notes,
                'created_at' => $e->created_at->format('Y-m-d H:i'),
            ])->values(),
            'deferredTaxEntries' => $deferredTaxEntries->map(fn($e) => [
                'id' => $e->id,
                'client_id' => $e->client_id,
                'client' => $e->client ? [
                    'id' => $e->client->id,
                    'name' => $e->client->name,
                    'phone_number' => $e->client->mobile,
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
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'accounting_period_id' => 'required|exists:accounting_periods,id',
            'client_id' => 'nullable|exists:clients,id',
            'tax_type' => 'required|in:current,deferred',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        TaxEntry::create($validated);

        return redirect()->back()->with('success', 'Tax entry added successfully.');
    }

    public function update(Request $request, TaxEntry $taxEntry)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $taxEntry->update($validated);

        return redirect()->back()->with('success', 'Tax entry updated successfully.');
    }

    public function destroy(TaxEntry $taxEntry)
    {
        $taxEntry->delete();

        return redirect()->back()->with('success', 'Tax entry deleted successfully.');
    }
}
