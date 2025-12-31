<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\Client;
use App\Models\CostOfSale;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CostOfSalesController extends Controller
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

        // Get cost of sales entries for this category and period
        $entries = CostOfSale::where('accounting_period_id', $period->id)
            ->where('category', $category)
            ->with('client:id,name,mobile')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate totals
        $totalAmount = $entries->sum('amount');

        // Group by subcategory and calculate totals
        $costBreakdown = $entries->groupBy('subcategory')
            ->map(fn($items) => $items->sum('amount'));

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
        ];

        return Inertia::render('Accounting/CostOfSales/Index', [
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
                'notes' => $e->notes,
                'created_at' => $e->created_at->format('Y-m-d H:i'),
            ]),
            'totalAmount' => (float) $totalAmount,
            'costBreakdown' => $costBreakdown,
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
            'category' => 'required|in:travel_tourism,manpower_exporting,student_package',
            'subcategory' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        CostOfSale::create($validated);

        return redirect()->back()->with('success', 'Cost of sales entry added successfully.');
    }

    public function update(Request $request, CostOfSale $costOfSale)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'subcategory' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $costOfSale->update($validated);

        return redirect()->back()->with('success', 'Cost of sales entry updated successfully.');
    }

    public function destroy(CostOfSale $costOfSale)
    {
        $costOfSale->delete();

        return redirect()->back()->with('success', 'Cost of sales entry deleted successfully.');
    }
}
