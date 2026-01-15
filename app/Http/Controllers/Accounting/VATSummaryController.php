<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\Client;
use App\Models\IncomeEntry;
use App\Models\VATPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class VATSummaryController extends Controller
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

        // Get all income entries for this period
        $entries = IncomeEntry::where('accounting_period_id', $period->id)
            ->where('vat_amount', '>', 0)
            ->orderBy('category', 'asc')
            ->orderBy('subcategory', 'asc')
            ->get();

        // Get all periods for selector
        $periods = AccountingPeriod::latest()->get();

        // Group by category and subcategory
        $vatByCategory = [
            'travel_tourism' => [],
            'manpower_exporting' => [],
            'student_package' => [],
            'other_income' => [],
        ];

        foreach ($entries as $entry) {
            $subcategory = $entry->subcategory;

            if (!isset($vatByCategory[$entry->category][$subcategory])) {
                $vatByCategory[$entry->category][$subcategory] = 0;
            }

            $vatByCategory[$entry->category][$subcategory] += $entry->vat_amount;
        }

        // Calculate category totals
        $categoryTotals = [
            'travel_tourism' => array_sum($vatByCategory['travel_tourism']),
            'manpower_exporting' => array_sum($vatByCategory['manpower_exporting']),
            'student_package' => array_sum($vatByCategory['student_package']),
            'other_income' => array_sum($vatByCategory['other_income']),
        ];

        // Calculate grand total
        $totalVat = array_sum($categoryTotals);

        // Format data for frontend
        $formattedVatData = [];
        foreach ($vatByCategory as $category => $subcategories) {
            if (empty($subcategories)) {
                continue;
            }

            $formattedVatData[$category] = [];
            foreach ($subcategories as $subcategory => $amount) {
                $formattedVatData[$category][] = [
                    'subcategory' => $subcategory,
                    'vat_amount' => (float) $amount,
                ];
            }
        }

        // Get clients with VAT receivable (clients with income entries that have VAT)
        $clientsWithVat = Client::whereHas('incomeEntries', function ($query) use ($period) {
            $query->where('accounting_period_id', $period->id)
                ->where('vat_amount', '>', 0);
        })
        ->with(['incomeEntries' => function ($query) use ($period) {
            $query->where('accounting_period_id', $period->id)
                ->where('vat_amount', '>', 0);
        }])
        ->get()
        ->map(function ($client) {
            $totalVat = $client->incomeEntries->sum('vat_amount');
            $paidAmount = (float) ($client->vat_paid_amount ?? 0);
            $unpaidAmount = max(0, $totalVat - $paidAmount);

            return [
                'id' => $client->id,
                'name' => $client->name,
                'total_vat_receivable' => (float) $totalVat,
                'vat_paid' => $unpaidAmount <= 0, // Dynamically calculate if fully paid
                'vat_paid_amount' => $paidAmount,
                'vat_unpaid_amount' => $unpaidAmount,
                'vat_chalan_number' => $client->vat_chalan_number,
                'vat_payment_date' => $client->vat_payment_date ? $client->vat_payment_date->format('Y-m-d') : null,
            ];
        });

        return Inertia::render('Accounting/VATSummary/Index', [
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
            'vatData' => $formattedVatData,
            'categoryTotals' => [
                'travel_tourism' => (float) $categoryTotals['travel_tourism'],
                'manpower_exporting' => (float) $categoryTotals['manpower_exporting'],
                'student_package' => (float) $categoryTotals['student_package'],
                'other_income' => (float) $categoryTotals['other_income'],
            ],
            'totalVat' => (float) $totalVat,
            'vatPayments' => $period->vatPayments()->latest()->get()->map(fn($payment) => [
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
            'totalVatPayments' => (float) $period->total_vat_payments,
            'vatBalance' => (float) $period->vat_balance,
            'clientsWithVat' => $clientsWithVat,
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

        $paymentType = $validated['payment_type'];

        // Handle bulk payment (all clients)
        if ($paymentType === 'bulk') {
            // Handle file upload
            if ($request->hasFile('chalan_slip')) {
                $validated['chalan_slip'] = $request->file('chalan_slip')->store('vat_payments', 'public');
            }

            // Get all clients with unpaid VAT
            $clientsWithVat = Client::whereHas('incomeEntries', function ($query) use ($period) {
                $query->where('accounting_period_id', $period->id)
                    ->where('vat_amount', '>', 0);
            })->get();

            $totalPaymentAmount = 0;

            // Create individual payment record for each client with unpaid VAT
            foreach ($clientsWithVat as $client) {
                $clientTotalVat = $client->incomeEntries()
                    ->where('accounting_period_id', $period->id)
                    ->where('vat_amount', '>', 0)
                    ->sum('vat_amount');

                $currentPaid = (float) ($client->vat_paid_amount ?? 0);
                $unpaidVat = max(0, $clientTotalVat - $currentPaid);

                // Skip clients with no unpaid VAT
                if ($unpaidVat <= 0) {
                    continue;
                }

                // Create payment record for this client
                $period->vatPayments()->create([
                    'payment_type' => 'bulk',
                    'client_id' => $client->id,
                    'payment_amount' => $unpaidVat,
                    'chalan_number' => $validated['chalan_number'],
                    'chalan_slip' => $validated['chalan_slip'] ?? null,
                    'payment_date' => $validated['payment_date'],
                    'notes' => $validated['notes'] ?? 'Part of bulk payment',
                ]);

                $totalPaymentAmount += $unpaidVat;

                // Update client VAT status
                $client->update([
                    'vat_paid' => true,
                    'vat_paid_amount' => $currentPaid + $unpaidVat,
                    'vat_chalan_number' => $validated['chalan_number'],
                    'vat_payment_date' => $validated['payment_date'],
                ]);
            }

            return redirect()->back()->with('success', 'Bulk VAT payment of ৳' . number_format($totalPaymentAmount, 2) . ' recorded successfully for ' . $clientsWithVat->count() . ' client(s).');
        }

        // Handle individual client payment
        if ($paymentType === 'individual') {
            if (!$validated['client_id']) {
                return redirect()->back()->withErrors([
                    'client_id' => 'Client is required for individual payment.'
                ])->withInput();
            }

            $client = Client::findOrFail($validated['client_id']);

            // Calculate client's total VAT and unpaid amount
            $clientVat = $client->incomeEntries()
                ->where('accounting_period_id', $period->id)
                ->where('vat_amount', '>', 0)
                ->sum('vat_amount');

            $currentPaid = (float) ($client->vat_paid_amount ?? 0);
            $unpaidVat = max(0, $clientVat - $currentPaid);

            if ($validated['payment_amount'] > $unpaidVat) {
                return redirect()->back()->withErrors([
                    'payment_amount' => 'Payment amount cannot exceed unpaid VAT. Unpaid VAT: ৳' . number_format($unpaidVat, 2)
                ])->withInput();
            }

            // Handle file upload
            if ($request->hasFile('chalan_slip')) {
                $validated['chalan_slip'] = $request->file('chalan_slip')->store('vat_payments', 'public');
            }

            $period->vatPayments()->create($validated);

            // Update client VAT status (add to existing paid amount)
            $newPaidAmount = $currentPaid + $validated['payment_amount'];
            $fullyPaid = $newPaidAmount >= $clientVat;

            $client->update([
                'vat_paid' => $fullyPaid,
                'vat_paid_amount' => $newPaidAmount,
                'vat_chalan_number' => $validated['chalan_number'],
                'vat_payment_date' => $validated['payment_date'],
            ]);

            return redirect()->back()->with('success', 'Individual VAT payment recorded successfully for ' . $client->name . '.');
        }

        return redirect()->back()->withErrors(['payment_type' => 'Invalid payment type.']);
    }

    public function deletePayment(VATPayment $payment)
    {
        // Check if this is part of a bulk payment (multiple records with same challan number)
        $isBulkPayment = VATPayment::where('accounting_period_id', $payment->accounting_period_id)
            ->where('chalan_number', $payment->chalan_number)
            ->where('payment_type', 'bulk')
            ->count() > 1;

        // If bulk payment, delete all related payment records
        if ($isBulkPayment) {
            $chalanNumber = $payment->chalan_number;
            $periodId = $payment->accounting_period_id;

            // Get all client IDs affected
            $affectedClientIds = VATPayment::where('accounting_period_id', $periodId)
                ->where('chalan_number', $chalanNumber)
                ->where('payment_type', 'bulk')
                ->pluck('client_id');

            // Reset all affected clients
            Client::whereIn('id', $affectedClientIds)->update([
                'vat_paid' => false,
                'vat_paid_amount' => 0,
                'vat_chalan_number' => null,
                'vat_payment_date' => null,
            ]);

            // Delete all payment records with this challan
            VATPayment::where('accounting_period_id', $periodId)
                ->where('chalan_number', $chalanNumber)
                ->where('payment_type', 'bulk')
                ->delete();

            // Delete file if exists
            if ($payment->chalan_slip && Storage::disk('public')->exists($payment->chalan_slip)) {
                Storage::disk('public')->delete($payment->chalan_slip);
            }

            return redirect()->back()->with('success', 'Bulk VAT payment deleted successfully for all clients.');
        }

        // Individual payment - reset only that client
        if ($payment->client_id) {
            Client::where('id', $payment->client_id)->update([
                'vat_paid' => false,
                'vat_paid_amount' => 0,
                'vat_chalan_number' => null,
                'vat_payment_date' => null,
            ]);
        }

        // Delete file if exists (only for individual payments or last bulk payment)
        if ($payment->chalan_slip && Storage::disk('public')->exists($payment->chalan_slip)) {
            Storage::disk('public')->delete($payment->chalan_slip);
        }

        $payment->delete();

        return redirect()->back()->with('success', 'VAT payment deleted successfully.');
    }

    public function report(Request $request)
    {
        $period = AccountingPeriod::where('status', 'active')
            ->orWhere('status', 'draft')
            ->latest()
            ->first();

        $entries = IncomeEntry::where('accounting_period_id', $period->id)
            ->where('vat_amount', '>', 0)
            ->orderBy('category', 'asc')
            ->orderBy('subcategory', 'asc')
            ->get();

        $vatByCategory = [];
        foreach ($entries as $entry) {
            if (!isset($vatByCategory[$entry->category])) {
                $vatByCategory[$entry->category] = [];
            }
            if (!isset($vatByCategory[$entry->category][$entry->subcategory])) {
                $vatByCategory[$entry->category][$entry->subcategory] = 0;
            }
            $vatByCategory[$entry->category][$entry->subcategory] += $entry->vat_amount;
        }

        $totalVat = $entries->sum('vat_amount');
        $totalVatPayments = $period->total_vat_payments;
        $vatBalance = $period->vat_balance;

        if ($request->query('type') === 'pdf') {
            $fileName = "vat-summary-report-" . now()->format('Y-m-d') . '.pdf';

            return Pdf::loadView('pdfs.vat_summary_report', [
                'period' => $period,
                'vatByCategory' => $vatByCategory,
                'totalVat' => $totalVat,
                'totalVatPayments' => $totalVatPayments,
                'vatBalance' => $vatBalance,
                'payments' => $period->vatPayments()->latest()->get(),
            ])->download($fileName);
        }

        $handle = fopen('php://memory', 'w');
        
        // Summary
        fputcsv($handle, ['VAT Summary for ' . $period->name]);
        fputcsv($handle, ['Metric', 'Amount']);
        fputcsv($handle, ['Total VAT Collected', $totalVat]);
        fputcsv($handle, ['Total Payments Made', $totalVatPayments]);
        fputcsv($handle, ['Remaining Balance', $vatBalance]);
        fputcsv($handle, []);

        // Payment History
        fputcsv($handle, ['VAT Payment History']);
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

        // VAT Breakdown
        fputcsv($handle, ['Detailed VAT Breakdown']);
        fputcsv($handle, ['Category', 'Subcategory', 'VAT Amount']);
        foreach ($vatByCategory as $category => $subcategories) {
            foreach ($subcategories as $subcategory => $amount) {
                fputcsv($handle, [$category, $subcategory, $amount]);
            }
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        $fileName = "vat-summary-report-" . now()->format('Y-m-d') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }
}
