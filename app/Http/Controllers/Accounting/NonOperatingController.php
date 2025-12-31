<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\Client;
use App\Models\NonOperatingEntry;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
        $clients = Client::select('id', 'name', 'mobile')
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
        $totalExpenses = $expenseEntries->sum('amount');
        $netNonOperating = $totalIncome - $totalExpenses;

        // Group by category for breakdown
        $incomeBreakdown = $incomeEntries->groupBy('category')
            ->map(fn($items) => $items->sum('amount'));

        $expenseBreakdown = $expenseEntries->groupBy('category')
            ->map(fn($items) => $items->sum('amount'));

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
                    'phone_number' => $e->client->mobile,
                ] : null,
                'category' => $e->category,
                'description' => $e->description,
                'amount' => (float) $e->amount,
                'notes' => $e->notes,
                'created_at' => $e->created_at->format('Y-m-d H:i'),
            ])->values(),
            'expenseEntries' => $expenseEntries->map(fn($e) => [
                'id' => $e->id,
                'client_id' => $e->client_id,
                'client' => $e->client ? [
                    'id' => $e->client->id,
                    'name' => $e->client->name,
                    'phone_number' => $e->client->mobile,
                ] : null,
                'category' => $e->category,
                'description' => $e->description,
                'amount' => (float) $e->amount,
                'notes' => $e->notes,
                'created_at' => $e->created_at->format('Y-m-d H:i'),
            ])->values(),
            'totalIncome' => (float) $totalIncome,
            'totalExpenses' => (float) $totalExpenses,
            'netNonOperating' => (float) $netNonOperating,
            'incomeBreakdown' => $incomeBreakdown,
            'expenseBreakdown' => $expenseBreakdown,
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
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        NonOperatingEntry::create($validated);

        return redirect()->back()->with('success', 'Non-operating entry added successfully.');
    }

    public function update(Request $request, NonOperatingEntry $nonOperatingEntry)
    {
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'category' => 'required|string|max:255',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $nonOperatingEntry->update($validated);

        return redirect()->back()->with('success', 'Non-operating entry updated successfully.');
    }

    public function destroy(NonOperatingEntry $nonOperatingEntry)
    {
        $nonOperatingEntry->delete();

        return redirect()->back()->with('success', 'Non-operating entry deleted successfully.');
    }
}
