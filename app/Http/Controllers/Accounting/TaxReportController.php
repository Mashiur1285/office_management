<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\LaravelPdf\Facades\Pdf;

class TaxReportController extends Controller
{
    public function index()
    {
        $periods = AccountingPeriod::latest()->get();

        $reportData = $periods->map(function ($period) {
            return [
                'id' => $period->id,
                'name' => $period->name,
                'type' => $period->type,
                'start_date' => $period->start_date->format('Y-m-d'),
                'end_date' => $period->end_date->format('Y-m-d'),
                'status' => $period->status,
                'total_tax' => (float) $period->total_tax,
                'total_payments' => (float) $period->total_tax_payments,
                'balance' => (float) $period->tax_balance,
                'payments' => $period->taxPayments()->latest()->get()->map(fn($payment) => [
                    'id' => $payment->id,
                    'payment_amount' => (float) $payment->payment_amount,
                    'chalan_number' => $payment->chalan_number,
                    'chalan_slip' => $payment->chalan_slip,
                    'payment_date' => $payment->payment_date->format('Y-m-d'),
                    'notes' => $payment->notes,
                ]),
            ];
        });

        return Inertia::render('Accounting/TaxReport/Index', [
            'periods' => $reportData,
        ]);
    }

    public function report(Request $request)
    {
        $periods = AccountingPeriod::latest()->get();

        $overallTotalTax = $periods->sum('total_tax');
        $overallTotalPayments = $periods->sum('total_tax_payments');
        $overallOutstandingBalance = $overallTotalTax - $overallTotalPayments;

        if ($request->query('type') === 'pdf') {
            $fileName = "tax-report-" . now()->format('Y-m-d') . '.pdf';

            return Pdf::view('pdfs.tax_overview_report', [
                'periods' => $periods,
                'overallTotalTax' => $overallTotalTax,
                'overallTotalPayments' => $overallTotalPayments,
                'overallOutstandingBalance' => $overallOutstandingBalance,
            ])->name($fileName);
        }

        $handle = fopen('php://memory', 'w');

        fputcsv($handle, ['Overall Tax Summary']);
        fputcsv($handle, ['Metric', 'Amount']);
        fputcsv($handle, ['Total Tax (All Periods)', $overallTotalTax]);
        fputcsv($handle, ['Total Payments (All Periods)', $overallTotalPayments]);
        fputcsv($handle, ['Outstanding Balance', $overallOutstandingBalance]);
        fputcsv($handle, []);

        fputcsv($handle, ['Per-Period Tax Summary']);
        fputcsv($handle, ['Period', 'Date Range', 'Total Tax', 'Payments', 'Balance', 'Status']);
        foreach ($periods as $period) {
            fputcsv($handle, [
                $period->name,
                $period->start_date->format('Y-m-d') . ' - ' . $period->end_date->format('Y-m-d'),
                $period->total_tax,
                $period->total_tax_payments,
                $period->tax_balance,
                $period->status,
            ]);
        }
        fputcsv($handle, []);

        fputcsv($handle, ['Detailed Payment History']);
        foreach ($periods as $period) {
            if ($period->taxPayments->count() > 0) {
                fputcsv($handle, ['Payments for Period: ' . $period->name]);
                fputcsv($handle, ['Date', 'Amount', 'Chalan Number', 'Notes']);
                foreach ($period->taxPayments as $payment) {
                    fputcsv($handle, [
                        $payment->payment_date->format('Y-m-d'),
                        $payment->payment_amount,
                        $payment->chalan_number,
                        $payment->notes,
                    ]);
                }
                fputcsv($handle, []);
            }
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $fileName = "tax-report-" . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
