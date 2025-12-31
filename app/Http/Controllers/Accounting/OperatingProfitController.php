<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use Inertia\Inertia;

class OperatingProfitController extends Controller
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

        $period->load(['incomeEntries', 'costOfSales', 'operatingExpenses']);

        // Get all periods for selector
        $periods = AccountingPeriod::latest()->get();

        // Calculate totals
        $grossProfit = $period->gross_profit;
        $totalOperatingExpenses = $period->total_operating_expenses;
        $totalOperatingExpensesVat = $period->total_operating_expenses_vat;
        $totalOperatingExpensesWithVat = $totalOperatingExpenses + $totalOperatingExpensesVat;
        $operatingProfit = $period->operating_profit;

        // Get operating expenses breakdown by category
        $expensesByCategory = [
            'employee_manpower' => [
                'amount' => $period->operatingExpenses()->where('category', 'employee_manpower')->sum('amount'),
                'vat_amount' => $period->operatingExpenses()->where('category', 'employee_manpower')->sum('vat_amount'),
            ],
            'administrative' => [
                'amount' => $period->operatingExpenses()->where('category', 'administrative')->sum('amount'),
                'vat_amount' => $period->operatingExpenses()->where('category', 'administrative')->sum('vat_amount'),
            ],
            'selling_marketing' => [
                'amount' => $period->operatingExpenses()->where('category', 'selling_marketing')->sum('amount'),
                'vat_amount' => $period->operatingExpenses()->where('category', 'selling_marketing')->sum('vat_amount'),
            ],
            'general' => [
                'amount' => $period->operatingExpenses()->where('category', 'general')->sum('amount'),
                'vat_amount' => $period->operatingExpenses()->where('category', 'general')->sum('vat_amount'),
            ],
        ];

        return Inertia::render('Accounting/OperatingProfit/Index', [
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
            'totalOperatingExpensesWithVat' => (float) $totalOperatingExpensesWithVat,
            'operatingProfit' => (float) $operatingProfit,
            'expensesByCategory' => [
                'employee_manpower' => [
                    'amount' => (float) $expensesByCategory['employee_manpower']['amount'],
                    'vat_amount' => (float) $expensesByCategory['employee_manpower']['vat_amount'],
                    'total' => (float) ($expensesByCategory['employee_manpower']['amount'] + $expensesByCategory['employee_manpower']['vat_amount']),
                ],
                'administrative' => [
                    'amount' => (float) $expensesByCategory['administrative']['amount'],
                    'vat_amount' => (float) $expensesByCategory['administrative']['vat_amount'],
                    'total' => (float) ($expensesByCategory['administrative']['amount'] + $expensesByCategory['administrative']['vat_amount']),
                ],
                'selling_marketing' => [
                    'amount' => (float) $expensesByCategory['selling_marketing']['amount'],
                    'vat_amount' => (float) $expensesByCategory['selling_marketing']['vat_amount'],
                    'total' => (float) ($expensesByCategory['selling_marketing']['amount'] + $expensesByCategory['selling_marketing']['vat_amount']),
                ],
                'general' => [
                    'amount' => (float) $expensesByCategory['general']['amount'],
                    'vat_amount' => (float) $expensesByCategory['general']['vat_amount'],
                    'total' => (float) ($expensesByCategory['general']['amount'] + $expensesByCategory['general']['vat_amount']),
                ],
            ],
        ]);
    }
}
