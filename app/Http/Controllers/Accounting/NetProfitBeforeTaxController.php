<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use Inertia\Inertia;

class NetProfitBeforeTaxController extends Controller
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

        $period->load(['incomeEntries', 'costOfSales', 'operatingExpenses', 'nonOperatingEntries']);

        // Get all periods for selector
        $periods = AccountingPeriod::latest()->get();

        // Calculate components
        $grossProfit = $period->gross_profit;
        $totalOperatingExpenses = $period->total_operating_expenses;
        $totalOperatingExpensesVat = $period->total_operating_expenses_vat;
        $operatingProfit = $period->operating_profit;

        $nonOperatingIncome = $period->non_operating_income;
        $nonOperatingExpenses = $period->non_operating_expenses;
        $netNonOperating = $period->net_non_operating;

        $netProfitBeforeTax = $period->net_profit_before_tax;

        return Inertia::render('Accounting/NetProfitBeforeTax/Index', [
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
            'grossProfit' => (float) $grossProfit,
            'totalOperatingExpenses' => (float) $totalOperatingExpenses,
            'totalOperatingExpensesVat' => (float) $totalOperatingExpensesVat,
            'operatingProfit' => (float) $operatingProfit,
            'nonOperatingIncome' => (float) $nonOperatingIncome,
            'nonOperatingExpenses' => (float) $nonOperatingExpenses,
            'netNonOperating' => (float) $netNonOperating,
            'netProfitBeforeTax' => (float) $netProfitBeforeTax,
        ]);
    }
}
