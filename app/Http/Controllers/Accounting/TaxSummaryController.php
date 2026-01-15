<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\Client;
use App\Models\TaxEntry;
use App\Models\TaxPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class TaxSummaryController extends Controller
{
    public function index()
    {
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

        $entries = TaxEntry::where('accounting_period_id', $period->id)
            ->orderBy('tax_type', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $periods = AccountingPeriod::latest()->get();

        $taxByType = [
            'current' => (float) $entries->where('tax_type', 'current')->sum('amount'),
            'deferred' => (float) $entries->where('tax_type', 'deferred')->sum('amount'),
        ];

        $totalTax = (float) $entries->sum('amount');
        $totalTaxPayments = (float) $period->total_tax_payments;
        $taxBalance = (float) $period->tax_balance;

        $payments = $period->taxPayments()->latest()->get();
        $paymentsByClient = $payments->groupBy('client_id');

        $clientsWithTax = Client::whereHas('taxEntries', function ($query) use ($period) {
            $query->where('accounting_period_id', $period->id);
        })
        ->with(['taxEntries' => function ($query) use ($period) {
            $query->where('accounting_period_id', $period->id);
        }])
        ->get()
        ->map(function ($client) use ($paymentsByClient) {
            $totalClientTax = (float) $client->taxEntries->sum('amount');
            $clientPayments = $paymentsByClient->get($client->id, collect());
            $paidAmount = (float) $clientPayments->sum('payment_amount');
            $unpaidAmount = max(0, $totalClientTax - $paidAmount);
            $latestPayment = $clientPayments->sortByDesc('payment_date')->first();

            return [
                'id' => $client->id,
                'name' => $client->name,
                'total_tax_receivable' => $totalClientTax,
                'tax_paid_amount' => $paidAmount,
                'tax_unpaid_amount' => $unpaidAmount,
                'tax_paid' => $unpaidAmount <= 0,
                'tax_chalan_number' => $latestPayment?->chalan_number,
                'tax_payment_date' => $latestPayment?->payment_date?->format('Y-m-d'),
            ];
        });

        return Inertia::render('Accounting/TaxSummary/Index', [
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
            'taxByType' => $taxByType,
            'totalTax' => $totalTax,
            'taxPayments' => $payments->map(fn($payment) => [
                'id' => $payment->id,
                'payment_amount' => (float) $payment->payment_amount,
                'payment_type' => $payment->payment_type,
                'client_id' => $payment->client_id,
                'client_name' => $payment->client ? $payment->client->name : null,
                'chalan_number' => $payment->chalan_number,
                'chalan_slip' => $payment->chalan_slip,
                'payment_date' => $payment->payment_date->format('Y-m-d'),
                'notes' => $payment->notes,
            ]),
            'totalTaxPayments' => $totalTaxPayments,
            'taxBalance' => $taxBalance,
            'clientsWithTax' => $clientsWithTax,
        ]);
    }

    public function storePayment(Request $request, AccountingPeriod $period)
    {
        $validated = $request->validate([
            'payment_type' => 'required|in:bulk,individual',
            'client_id' => 'nullable|exists:clients,id',
            'payment_amount' => 'nullable|numeric|min:0',
            'chalan_number' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'chalan_slip' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'notes' => 'nullable|string',
        ]);

        if ($validated['payment_type'] === 'bulk') {
            if ($request->hasFile('chalan_slip')) {
                $validated['chalan_slip'] = $request->file('chalan_slip')->store('tax_payments', 'public');
            }

            $clientsWithTax = Client::whereHas('taxEntries', function ($query) use ($period) {
                $query->where('accounting_period_id', $period->id);
            })->with(['taxEntries' => function ($query) use ($period) {
                $query->where('accounting_period_id', $period->id);
            }])->get();

            $totalPaymentAmount = 0;

            foreach ($clientsWithTax as $client) {
                $clientTotalTax = (float) $client->taxEntries->sum('amount');
                $currentPaid = (float) $period->taxPayments()
                    ->where('client_id', $client->id)
                    ->sum('payment_amount');
                $unpaidTax = max(0, $clientTotalTax - $currentPaid);

                if ($unpaidTax <= 0) {
                    continue;
                }

                $period->taxPayments()->create([
                    'payment_type' => 'bulk',
                    'client_id' => $client->id,
                    'payment_amount' => $unpaidTax,
                    'chalan_number' => $validated['chalan_number'],
                    'chalan_slip' => $validated['chalan_slip'] ?? null,
                    'payment_date' => $validated['payment_date'],
                    'notes' => $validated['notes'] ?? 'Part of bulk payment',
                ]);

                $totalPaymentAmount += $unpaidTax;
            }

            $orgWideTax = (float) TaxEntry::where('accounting_period_id', $period->id)
                ->whereNull('client_id')
                ->sum('amount');
            $orgPaid = (float) $period->taxPayments()->whereNull('client_id')->sum('payment_amount');
            $orgUnpaid = max(0, $orgWideTax - $orgPaid);

            if ($orgUnpaid > 0) {
                $period->taxPayments()->create([
                    'payment_type' => 'bulk',
                    'client_id' => null,
                    'payment_amount' => $orgUnpaid,
                    'chalan_number' => $validated['chalan_number'],
                    'chalan_slip' => $validated['chalan_slip'] ?? null,
                    'payment_date' => $validated['payment_date'],
                    'notes' => $validated['notes'] ?? 'Organization-wide tax payment',
                ]);

                $totalPaymentAmount += $orgUnpaid;
            }

            return redirect()->back()->with('success', 'Bulk tax payment recorded successfully. Total: ৳' . number_format($totalPaymentAmount, 2));
        }

        if ($validated['payment_type'] === 'individual') {
            if (!$validated['client_id']) {
                return redirect()->back()->withErrors(['client_id' => 'Please select a client for individual payment.'])->withInput();
            }

            $clientTotalTax = (float) TaxEntry::where('accounting_period_id', $period->id)
                ->where('client_id', $validated['client_id'])
                ->sum('amount');
            $currentPaid = (float) $period->taxPayments()
                ->where('client_id', $validated['client_id'])
                ->sum('payment_amount');
            $unpaidTax = max(0, $clientTotalTax - $currentPaid);

            if (($validated['payment_amount'] ?? 0) > $unpaidTax) {
                return redirect()->back()->withErrors([
                    'payment_amount' => 'Payment amount cannot exceed unpaid tax. Unpaid tax: ৳' . number_format($unpaidTax, 2)
                ])->withInput();
            }

            if ($request->hasFile('chalan_slip')) {
                $validated['chalan_slip'] = $request->file('chalan_slip')->store('tax_payments', 'public');
            }

            $period->taxPayments()->create($validated);

            return redirect()->back()->with('success', 'Individual tax payment recorded successfully.');
        }

        return redirect()->back()->withErrors(['payment_type' => 'Invalid payment type.']);
    }

    public function deletePayment(TaxPayment $payment)
    {
        $isBulkPayment = TaxPayment::where('accounting_period_id', $payment->accounting_period_id)
            ->where('chalan_number', $payment->chalan_number)
            ->where('payment_type', 'bulk')
            ->count() > 1;

        if ($isBulkPayment) {
            $chalanNumber = $payment->chalan_number;
            $periodId = $payment->accounting_period_id;

            TaxPayment::where('accounting_period_id', $periodId)
                ->where('chalan_number', $chalanNumber)
                ->where('payment_type', 'bulk')
                ->delete();

            if ($payment->chalan_slip && Storage::disk('public')->exists($payment->chalan_slip)) {
                Storage::disk('public')->delete($payment->chalan_slip);
            }

            return redirect()->back()->with('success', 'Bulk tax payment deleted successfully.');
        }

        if ($payment->chalan_slip && Storage::disk('public')->exists($payment->chalan_slip)) {
            Storage::disk('public')->delete($payment->chalan_slip);
        }

        $payment->delete();

        return redirect()->back()->with('success', 'Tax payment deleted successfully.');
    }

    public function report(Request $request)
    {
        $period = AccountingPeriod::where('status', 'active')
            ->orWhere('status', 'draft')
            ->latest()
            ->first();

        $entries = TaxEntry::where('accounting_period_id', $period->id)
            ->orderBy('tax_type', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        $taxByType = [
            'current' => (float) $entries->where('tax_type', 'current')->sum('amount'),
            'deferred' => (float) $entries->where('tax_type', 'deferred')->sum('amount'),
        ];

        $totalTax = (float) $entries->sum('amount');
        $totalTaxPayments = (float) $period->total_tax_payments;
        $taxBalance = (float) $period->tax_balance;

        if ($request->query('type') === 'pdf') {
            $fileName = "tax-summary-report-" . now()->format('Y-m-d') . '.pdf';

            return Pdf::loadView('pdfs.tax_summary_report', [
                'period' => $period,
                'taxByType' => $taxByType,
                'totalTax' => $totalTax,
                'totalTaxPayments' => $totalTaxPayments,
                'taxBalance' => $taxBalance,
                'payments' => $period->taxPayments()->latest()->get(),
                'entries' => $entries,
            ])->download($fileName);
        }

        $handle = fopen('php://memory', 'w');

        fputcsv($handle, ['Tax Summary for ' . $period->name]);
        fputcsv($handle, ['Metric', 'Amount']);
        fputcsv($handle, ['Total Tax', $totalTax]);
        fputcsv($handle, ['Total Payments Made', $totalTaxPayments]);
        fputcsv($handle, ['Remaining Balance', $taxBalance]);
        fputcsv($handle, []);

        fputcsv($handle, ['Tax Breakdown']);
        fputcsv($handle, ['Type', 'Amount']);
        fputcsv($handle, ['Current Tax', $taxByType['current']]);
        fputcsv($handle, ['Deferred Tax', $taxByType['deferred']]);
        fputcsv($handle, []);

        fputcsv($handle, ['Tax Payment History']);
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

        fputcsv($handle, ['Detailed Tax Entries']);
        fputcsv($handle, ['Type', 'Description', 'Client', 'Amount', 'Date']);
        foreach ($entries as $entry) {
            fputcsv($handle, [
                ucfirst($entry->tax_type),
                $entry->description,
                $entry->client->name ?? 'N/A',
                $entry->amount,
                $entry->created_at->format('Y-m-d H:i'),
            ]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $fileName = "tax-summary-report-" . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
