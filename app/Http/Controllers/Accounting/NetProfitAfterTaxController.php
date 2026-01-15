<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class NetProfitAfterTaxController extends Controller
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

        $period->load(['incomeEntries', 'costOfSales', 'operatingExpenses', 'nonOperatingEntries', 'taxEntries']);

        // Get all periods for selector
        $periods = AccountingPeriod::latest()->get();

        // Calculate all P&L components for comprehensive display
        $totalIncome = $period->total_income;
        $totalVat = $period->total_vat;
        $totalCostOfSales = $period->total_cost_of_sales;
        $grossProfit = $period->gross_profit;

        $totalOperatingExpenses = $period->total_operating_expenses;
        $totalOperatingExpensesVat = $period->total_operating_expenses_vat;
        $operatingProfit = $period->operating_profit;

        $nonOperatingIncome = $period->non_operating_income;
        $nonOperatingExpenses = $period->non_operating_expenses;
        $netNonOperating = $period->net_non_operating;

        $netProfitBeforeTax = $period->net_profit_before_tax;

        // Tax breakdown
        $currentTax = $period->taxEntries()->where('tax_type', 'current')->sum('amount');
        $deferredTax = $period->taxEntries()->where('tax_type', 'deferred')->sum('amount');
        $totalTax = $period->total_tax;

        $netProfitAfterTax = $period->net_profit_after_tax;

        return Inertia::render('Accounting/NetProfitAfterTax/Index', [
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
            'totalOperatingExpenses' => (float) $totalOperatingExpenses,
            'totalOperatingExpensesVat' => (float) $totalOperatingExpensesVat,
            'operatingProfit' => (float) $operatingProfit,
            'nonOperatingIncome' => (float) $nonOperatingIncome,
            'nonOperatingExpenses' => (float) $nonOperatingExpenses,
            'netNonOperating' => (float) $netNonOperating,
            'netProfitBeforeTax' => (float) $netProfitBeforeTax,
            'currentTax' => (float) $currentTax,
            'deferredTax' => (float) $deferredTax,
            'totalTax' => (float) $totalTax,
            'netProfitAfterTax' => (float) $netProfitAfterTax,
        ]);
    }

    public function report(Request $request)
    {
        $period = AccountingPeriod::where('status', 'active')
            ->orWhere('status', 'draft')
            ->latest()
            ->first();

        $period->load(['incomeEntries', 'costOfSales', 'operatingExpenses', 'nonOperatingEntries', 'taxEntries']);

        $totalIncome = $period->total_income;
        $totalCostOfSales = $period->total_cost_of_sales;
        $grossProfit = $period->gross_profit;
        $operatingProfit = $period->operating_profit;
        $netNonOperating = $period->net_non_operating;
        $netProfitBeforeTax = $period->net_profit_before_tax;
        $totalTax = $period->total_tax;
        $netProfitAfterTax = $period->net_profit_after_tax;

        if ($request->query('type') === 'pdf') {
            $fileName = "profit-and-loss-report-" . now()->format('Y-m-d') . '.pdf';

            return Pdf::loadView('pdfs.net_profit_after_tax_report', [
                'period' => $period,
                'totalIncome' => $totalIncome,
                'totalCostOfSales' => $totalCostOfSales,
                'grossProfit' => $grossProfit,
                'operatingProfit' => $operatingProfit,
                'totalOperatingExpenses' => $period->total_operating_expenses,
                'netNonOperating' => $netNonOperating,
                'netProfitBeforeTax' => $netProfitBeforeTax,
                'totalTax' => $totalTax,
                'netProfitAfterTax' => $netProfitAfterTax,
            ])->download($fileName);
        }

        $handle = fopen('php://memory', 'w');

        fputcsv($handle, ['Profit & Loss Statement for ' . $period->name]);
        fputcsv($handle, ['Metric', 'Amount']);
        fputcsv($handle, []);

        fputcsv($handle, ['Total Income', $totalIncome]);
        fputcsv($handle, ['Less: Cost of Sales', $totalCostOfSales]);
        fputcsv($handle, ['Gross Profit', $grossProfit]);
        fputcsv($handle, []);

        fputcsv($handle, ['Less: Operating Expenses', $period->total_operating_expenses]);
        fputcsv($handle, ['Operating Profit', $operatingProfit]);
        fputcsv($handle, []);

        fputcsv($handle, ['Add/Less: Net Non-Operating', $netNonOperating]);
        fputcsv($handle, ['Net Profit Before Tax', $netProfitBeforeTax]);
        fputcsv($handle, []);

        fputcsv($handle, ['Less: Total Tax Expenses', $totalTax]);
        fputcsv($handle, ['Net Profit After Tax', $netProfitAfterTax]);

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $fileName = "profit-and-loss-report-" . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
