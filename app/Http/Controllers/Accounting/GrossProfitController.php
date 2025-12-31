<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use Inertia\Inertia;

class GrossProfitController extends Controller
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

        $period->load(['incomeEntries', 'costOfSales']);

        // Get all periods for selector
        $periods = AccountingPeriod::latest()->get();

        // Calculate totals
        $totalIncome = $period->total_income;
        $totalVat = $period->total_vat;
        $totalCostOfSales = $period->total_cost_of_sales;
        $grossProfit = $period->gross_profit;

        // Get income breakdown by category
        $incomeByCategory = [
            'travel_tourism' => $period->incomeEntries()->where('category', 'travel_tourism')->sum('amount'),
            'manpower_exporting' => $period->incomeEntries()->where('category', 'manpower_exporting')->sum('amount'),
            'student_package' => $period->incomeEntries()->where('category', 'student_package')->sum('amount'),
            'other_income' => $period->incomeEntries()->where('category', 'other_income')->sum('amount'),
        ];

        // Get cost of sales breakdown by category
        $costsByCategory = [
            'travel_tourism' => $period->costOfSales()->where('category', 'travel_tourism')->sum('amount'),
            'manpower_exporting' => $period->costOfSales()->where('category', 'manpower_exporting')->sum('amount'),
            'student_package' => $period->costOfSales()->where('category', 'student_package')->sum('amount'),
        ];

        return Inertia::render('Accounting/GrossProfit/Index', [
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
            'totalIncome' => (float) $totalIncome,
            'totalVat' => (float) $totalVat,
            'totalCostOfSales' => (float) $totalCostOfSales,
            'grossProfit' => (float) $grossProfit,
            'incomeByCategory' => [
                'travel_tourism' => (float) $incomeByCategory['travel_tourism'],
                'manpower_exporting' => (float) $incomeByCategory['manpower_exporting'],
                'student_package' => (float) $incomeByCategory['student_package'],
                'other_income' => (float) $incomeByCategory['other_income'],
            ],
            'costsByCategory' => [
                'travel_tourism' => (float) $costsByCategory['travel_tourism'],
                'manpower_exporting' => (float) $costsByCategory['manpower_exporting'],
                'student_package' => (float) $costsByCategory['student_package'],
            ],
        ]);
    }
}
