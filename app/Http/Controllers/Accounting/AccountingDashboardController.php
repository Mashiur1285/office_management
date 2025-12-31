<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use Inertia\Inertia;

class AccountingDashboardController extends Controller
{
    public function index()
    {
        // Get the current active period or latest period
        $period = AccountingPeriod::where('status', 'active')
            ->orWhere('status', 'draft')
            ->latest()
            ->first();

        if (!$period) {
            // Create a default period if none exists
            $period = AccountingPeriod::create([
                'name' => now()->format('F Y'),
                'type' => 'monthly',
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->endOfMonth(),
                'status' => 'draft',
            ]);
        }

        $period->load([
            'incomeEntries',
            'costOfSales',
            'operatingExpenses',
            'nonOperatingEntries',
            'taxEntries',
        ]);

        // Get all periods for selector
        $periods = AccountingPeriod::latest()->get();

        // Income breakdown by category
        $incomeBreakdown = [
            'travel_tourism' => $period->incomeEntries()->where('category', 'travel_tourism')->sum('amount'),
            'manpower_exporting' => $period->incomeEntries()->where('category', 'manpower_exporting')->sum('amount'),
            'student_package' => $period->incomeEntries()->where('category', 'student_package')->sum('amount'),
            'other_income' => $period->incomeEntries()->where('category', 'other_income')->sum('amount'),
        ];

        // Expense breakdown (Operating Expenses by category)
        $expenseBreakdown = [
            'employee_manpower' => $period->operatingExpenses()->where('category', 'employee_manpower')->sum('amount'),
            'administrative' => $period->operatingExpenses()->where('category', 'administrative')->sum('amount'),
            'selling_marketing' => $period->operatingExpenses()->where('category', 'selling_marketing')->sum('amount'),
            'general' => $period->operatingExpenses()->where('category', 'general')->sum('amount'),
        ];

        // P&L Overview for pie chart
        $plOverview = [
            'revenue' => (float) $period->total_income,
            'cost_of_sales' => (float) $period->total_cost_of_sales,
            'operating_expenses' => (float) $period->total_operating_expenses,
            'tax' => (float) $period->total_tax,
            'net_profit' => (float) $period->net_profit_after_tax,
        ];

        return Inertia::render('Accounting/Dashboard', [
            'period' => [
                'id' => $period->id,
                'name' => $period->name,
                'type' => $period->type,
                'status' => $period->status,
                'start_date' => $period->start_date->format('Y-m-d'),
                'end_date' => $period->end_date->format('Y-m-d'),
                'total_income' => (float) $period->total_income,
                'total_vat' => (float) $period->total_vat,
                'total_cost_of_sales' => (float) $period->total_cost_of_sales,
                'gross_profit' => (float) $period->gross_profit,
                'total_operating_expenses' => (float) $period->total_operating_expenses,
                'operating_profit' => (float) $period->operating_profit,
                'non_operating_income' => (float) $period->non_operating_income,
                'non_operating_expenses' => (float) $period->non_operating_expenses,
                'net_non_operating' => (float) $period->net_non_operating,
                'net_profit_before_tax' => (float) $period->net_profit_before_tax,
                'total_tax' => (float) $period->total_tax,
                'net_profit_after_tax' => (float) $period->net_profit_after_tax,
            ],
            'periods' => $periods->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'type' => $p->type,
                'status' => $p->status,
            ]),
            'incomeBreakdown' => [
                'travel_tourism' => (float) $incomeBreakdown['travel_tourism'],
                'manpower_exporting' => (float) $incomeBreakdown['manpower_exporting'],
                'student_package' => (float) $incomeBreakdown['student_package'],
                'other_income' => (float) $incomeBreakdown['other_income'],
            ],
            'expenseBreakdown' => [
                'employee_manpower' => (float) $expenseBreakdown['employee_manpower'],
                'administrative' => (float) $expenseBreakdown['administrative'],
                'selling_marketing' => (float) $expenseBreakdown['selling_marketing'],
                'general' => (float) $expenseBreakdown['general'],
            ],
            'plOverview' => $plOverview,
        ]);
    }
}
