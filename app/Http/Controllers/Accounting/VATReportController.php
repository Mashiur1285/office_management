<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\LaravelPdf\Facades\Pdf;

class VATReportController extends Controller
{
    public function index()
    {
        // Get all periods with their VAT data
        $periods = AccountingPeriod::latest()->get();

        $reportData = $periods->map(function ($period) {
            return [
                'id' => $period->id,
                'name' => $period->name,
                'type' => $period->type,
                'start_date' => $period->start_date->format('Y-m-d'),
                'end_date' => $period->end_date->format('Y-m-d'),
                'status' => $period->status,
                'total_vat' => (float) $period->total_vat,
                'total_payments' => (float) $period->total_vat_payments,
                'balance' => (float) $period->vat_balance,
                'payments' => $period->vatPayments()->latest()->get()->map(fn($payment) => [
                    'id' => $payment->id,
                    'payment_amount' => (float) $payment->payment_amount,
                    'chalan_number' => $payment->chalan_number,
                    'chalan_slip' => $payment->chalan_slip,
                    'payment_date' => $payment->payment_date->format('Y-m-d'),
                    'notes' => $payment->notes,
                ]),
            ];
        });

        return Inertia::render('Accounting/VATReport/Index', [
            'periods' => $reportData,
        ]);
    }

    public function report(Request $request)
    {
        $periods = AccountingPeriod::latest()->get();

        $overallTotalVat = $periods->sum('total_vat');
        $overallTotalPayments = $periods->sum('total_vat_payments');
        $overallOutstandingBalance = $overallTotalVat - $overallTotalPayments;

        if ($request->query('type') === 'pdf') {
            $fileName = "vat-report-" . now()->format('Y-m-d') . '.pdf';

            return Pdf::view('pdfs.vat_report', [
                'periods' => $periods,
                'overallTotalVat' => $overallTotalVat,
                'overallTotalPayments' => $overallTotalPayments,
                'overallOutstandingBalance' => $overallOutstandingBalance,
            ])->name($fileName);
        }

        $handle = fopen('php://memory', 'w');

        // Overall Summary
        fputcsv($handle, ['Overall VAT Summary']);
        fputcsv($handle, ['Metric', 'Amount']);
        fputcsv($handle, ['Total VAT (All Periods)', $overallTotalVat]);
        fputcsv($handle, ['Total Payments (All Periods)', $overallTotalPayments]);
        fputcsv($handle, ['Outstanding Balance', $overallOutstandingBalance]);
        fputcsv($handle, []);

        // Per-Period VAT Summary
        fputcsv($handle, ['Per-Period VAT Summary']);
        fputcsv($handle, ['Period', 'Date Range', 'Total VAT', 'Payments', 'Balance', 'Status']);
        foreach ($periods as $period) {
            fputcsv($handle, [
                $period->name,
                $period->start_date->format('Y-m-d') . ' - ' . $period->end_date->format('Y-m-d'),
                $period->total_vat,
                $period->total_vat_payments,
                $period->vat_balance,
                $period->status,
            ]);
        }
        fputcsv($handle, []);

        // Detailed Payment History for each period
        fputcsv($handle, ['Detailed Payment History']);
        foreach ($periods as $period) {
            if ($period->vatPayments->count() > 0) {
                fputcsv($handle, ['Payments for Period: ' . $period->name]);
                fputcsv($handle, ['Date', 'Amount', 'Chalan Number', 'Notes']);
                foreach ($period->vatPayments as $payment) {
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

        $fileName = "vat-report-" . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
