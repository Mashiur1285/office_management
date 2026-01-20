<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

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
        $totalOperatingExpensesTax = $period->total_operating_expenses_tax;
        $totalOperatingExpensesWithVatTax = $totalOperatingExpenses + $totalOperatingExpensesVat + $totalOperatingExpensesTax;
        $operatingProfit = $period->operating_profit;

        // Get operating expenses breakdown by category
        $expensesByCategory = [
            'employee_manpower' => [
                'amount' => $period->operatingExpenses()->where('category', 'employee_manpower')->sum('amount'),
                'vat_amount' => $period->operatingExpenses()->where('category', 'employee_manpower')->sum('vat_amount'),
                'tax_amount' => $period->operatingExpenses()->where('category', 'employee_manpower')->sum('tax_amount'),
            ],
            'administrative' => [
                'amount' => $period->operatingExpenses()->where('category', 'administrative')->sum('amount'),
                'vat_amount' => $period->operatingExpenses()->where('category', 'administrative')->sum('vat_amount'),
                'tax_amount' => $period->operatingExpenses()->where('category', 'administrative')->sum('tax_amount'),
            ],
            'selling_marketing' => [
                'amount' => $period->operatingExpenses()->where('category', 'selling_marketing')->sum('amount'),
                'vat_amount' => $period->operatingExpenses()->where('category', 'selling_marketing')->sum('vat_amount'),
                'tax_amount' => $period->operatingExpenses()->where('category', 'selling_marketing')->sum('tax_amount'),
            ],
            'general' => [
                'amount' => $period->operatingExpenses()->where('category', 'general')->sum('amount'),
                'vat_amount' => $period->operatingExpenses()->where('category', 'general')->sum('vat_amount'),
                'tax_amount' => $period->operatingExpenses()->where('category', 'general')->sum('tax_amount'),
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
            'totalOperatingExpensesTax' => (float) $totalOperatingExpensesTax,
            'totalOperatingExpensesWithVatTax' => (float) $totalOperatingExpensesWithVatTax,
            'operatingProfit' => (float) $operatingProfit,
            'expensesByCategory' => [
                'employee_manpower' => [
                    'amount' => (float) $expensesByCategory['employee_manpower']['amount'],
                    'vat_amount' => (float) $expensesByCategory['employee_manpower']['vat_amount'],
                    'tax_amount' => (float) $expensesByCategory['employee_manpower']['tax_amount'],
                    'total' => (float) ($expensesByCategory['employee_manpower']['amount'] + $expensesByCategory['employee_manpower']['vat_amount'] + $expensesByCategory['employee_manpower']['tax_amount']),
                ],
                'administrative' => [
                    'amount' => (float) $expensesByCategory['administrative']['amount'],
                    'vat_amount' => (float) $expensesByCategory['administrative']['vat_amount'],
                    'tax_amount' => (float) $expensesByCategory['administrative']['tax_amount'],
                    'total' => (float) ($expensesByCategory['administrative']['amount'] + $expensesByCategory['administrative']['vat_amount'] + $expensesByCategory['administrative']['tax_amount']),
                ],
                'selling_marketing' => [
                    'amount' => (float) $expensesByCategory['selling_marketing']['amount'],
                    'vat_amount' => (float) $expensesByCategory['selling_marketing']['vat_amount'],
                    'tax_amount' => (float) $expensesByCategory['selling_marketing']['tax_amount'],
                    'total' => (float) ($expensesByCategory['selling_marketing']['amount'] + $expensesByCategory['selling_marketing']['vat_amount'] + $expensesByCategory['selling_marketing']['tax_amount']),
                ],
                'general' => [
                    'amount' => (float) $expensesByCategory['general']['amount'],
                    'vat_amount' => (float) $expensesByCategory['general']['vat_amount'],
                    'tax_amount' => (float) $expensesByCategory['general']['tax_amount'],
                    'total' => (float) ($expensesByCategory['general']['amount'] + $expensesByCategory['general']['vat_amount'] + $expensesByCategory['general']['tax_amount']),
                ],
            ],
        ]);
    }

    public function report(Request $request)
    {
        $period = AccountingPeriod::where('status', 'active')
            ->orWhere('status', 'draft')
            ->latest()
            ->first();

        $period->load(['incomeEntries', 'costOfSales', 'operatingExpenses']);

        $grossProfit = $period->gross_profit;
        $totalOperatingExpenses = $period->total_operating_expenses;
        $totalOperatingExpensesVat = $period->total_operating_expenses_vat;
        $totalOperatingExpensesTax = $period->total_operating_expenses_tax;
        $totalOperatingExpensesWithVatTax = $totalOperatingExpenses + $totalOperatingExpensesVat + $totalOperatingExpensesTax;
        $operatingProfit = $period->operating_profit;
        $operatingMargin = $grossProfit > 0 ? (($operatingProfit / $grossProfit) * 100) : 0;

        $expensesByCategory = [
            'employee_manpower' => [
                'amount' => $period->operatingExpenses()->where('category', 'employee_manpower')->sum('amount'),
                'vat_amount' => $period->operatingExpenses()->where('category', 'employee_manpower')->sum('vat_amount'),
                'tax_amount' => $period->operatingExpenses()->where('category', 'employee_manpower')->sum('tax_amount'),
            ],
            'administrative' => [
                'amount' => $period->operatingExpenses()->where('category', 'administrative')->sum('amount'),
                'vat_amount' => $period->operatingExpenses()->where('category', 'administrative')->sum('vat_amount'),
                'tax_amount' => $period->operatingExpenses()->where('category', 'administrative')->sum('tax_amount'),
            ],
            'selling_marketing' => [
                'amount' => $period->operatingExpenses()->where('category', 'selling_marketing')->sum('amount'),
                'vat_amount' => $period->operatingExpenses()->where('category', 'selling_marketing')->sum('vat_amount'),
                'tax_amount' => $period->operatingExpenses()->where('category', 'selling_marketing')->sum('tax_amount'),
            ],
            'general' => [
                'amount' => $period->operatingExpenses()->where('category', 'general')->sum('amount'),
                'vat_amount' => $period->operatingExpenses()->where('category', 'general')->sum('vat_amount'),
                'tax_amount' => $period->operatingExpenses()->where('category', 'general')->sum('tax_amount'),
            ],
        ];

        $categories = [
            'employee_manpower' => 'Employee & Manpower',
            'administrative' => 'Administrative',
            'selling_marketing' => 'Selling & Marketing',
            'general' => 'General',
        ];

        if ($request->query('type') === 'pdf') {
            $fileName = 'operating-profit-report-' . now()->format('Y-m-d') . '.pdf';

            return Pdf::loadView('pdfs.operating_profit_report', [
                'period' => $period,
                'grossProfit' => $grossProfit,
                'totalOperatingExpenses' => $totalOperatingExpenses,
                'totalOperatingExpensesVat' => $totalOperatingExpensesVat,
                'totalOperatingExpensesTax' => $totalOperatingExpensesTax,
                'totalOperatingExpensesWithVatTax' => $totalOperatingExpensesWithVatTax,
                'operatingProfit' => $operatingProfit,
                'operatingMargin' => $operatingMargin,
                'categories' => $categories,
                'expensesByCategory' => $expensesByCategory,
            ])->download($fileName);
        }

        $handle = fopen('php://memory', 'w');

        fputcsv($handle, ['Operating Profit Summary']);
        fputcsv($handle, ['Metric', 'Amount']);
        fputcsv($handle, ['Gross Profit', $grossProfit]);
        fputcsv($handle, ['Operating Expenses', $totalOperatingExpenses]);
        fputcsv($handle, ['Operating Expenses VAT', $totalOperatingExpensesVat]);
        fputcsv($handle, ['Operating Expenses Tax', $totalOperatingExpensesTax]);
        fputcsv($handle, ['Operating Expenses Total', $totalOperatingExpensesWithVatTax]);
        fputcsv($handle, ['Operating Profit', $operatingProfit]);
        fputcsv($handle, ['Operating Margin', number_format($operatingMargin, 2) . '%']);
        fputcsv($handle, []);

        fputcsv($handle, ['Operating Expenses Breakdown']);
        fputcsv($handle, ['Category', 'Amount', 'VAT', 'Tax', 'Total']);

        foreach ($categories as $key => $name) {
            $amount = $expensesByCategory[$key]['amount'] ?? 0;
            $vatAmount = $expensesByCategory[$key]['vat_amount'] ?? 0;
            $taxAmount = $expensesByCategory[$key]['tax_amount'] ?? 0;
            $total = $amount + $vatAmount + $taxAmount;

            fputcsv($handle, [$name, $amount, $vatAmount, $taxAmount, $total]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $fileName = 'operating-profit-report-' . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
