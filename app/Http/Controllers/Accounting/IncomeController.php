<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\Client;
use App\Models\IncomeEntry;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IncomeController extends Controller
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

        // Get income entries for this category and period
        $entries = IncomeEntry::where('accounting_period_id', $period->id)
            ->where('category', $category)
            ->with('client:id,name,mobile')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate totals
        $totalAmount = $entries->sum('amount');
        $totalVat = $entries->sum('vat_amount');
        $totalWithVat = $totalAmount + $totalVat;

        // Group by subcategory and calculate totals
        $incomeBreakdown = $entries->groupBy('subcategory')
            ->map(fn($items) => [
                'amount' => $items->sum('amount'),
                'vat_amount' => $items->sum('vat_amount'),
                'total' => $items->sum('amount') + $items->sum('vat_amount'),
            ]);

        // Get all periods for selector
        $periods = AccountingPeriod::latest()->get();

        // Get all clients for selection dropdown
        $clients = Client::select('id', 'name', 'mobile')
            ->orderBy('name')
            ->get();

        // Map category to display name
        $categoryNames = [
            'travel_tourism' => 'Travel & Tourism',
            'manpower_exporting' => 'Manpower Exporting',
            'student_package' => 'Student Package',
            'other_income' => 'Other Income',
        ];

        return Inertia::render('Accounting/Income/Index', [
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
                'client' => $e->client ? [
                    'id' => $e->client->id,
                    'name' => $e->client->name,
                    'phone_number' => $e->client->mobile,
                ] : null,
                'subcategory' => $e->subcategory,
                'description' => $e->description,
                'amount' => (float) $e->amount,
                'vat_rate' => (float) $e->vat_rate,
                'vat_amount' => (float) $e->vat_amount,
                'notes' => $e->notes,
                'created_at' => $e->created_at->format('Y-m-d H:i'),
            ]),
            'totalAmount' => (float) $totalAmount,
            'totalVat' => (float) $totalVat,
            'totalWithVat' => (float) $totalWithVat,
            'incomeBreakdown' => $incomeBreakdown,
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
            'category' => 'required|in:travel_tourism,manpower_exporting,student_package,other_income',
            'subcategory' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['vat_rate'] = $validated['vat_rate'] ?? 0;

        IncomeEntry::create($validated);

        return redirect()->back()->with('success', 'Income entry added successfully.');
    }

    public function update(Request $request, IncomeEntry $incomeEntry)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'subcategory' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $validated['vat_rate'] = $validated['vat_rate'] ?? 0;

        $incomeEntry->update($validated);

        return redirect()->back()->with('success', 'Income entry updated successfully.');
    }

    public function destroy(IncomeEntry $incomeEntry)
    {
        $incomeEntry->delete();

        return redirect()->back()->with('success', 'Income entry deleted successfully.');
    }
}
