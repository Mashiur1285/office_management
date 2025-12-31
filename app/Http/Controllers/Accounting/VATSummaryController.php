<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\AccountingPeriod;
use App\Models\IncomeEntry;
use Illuminate\Http\Request;
use Inertia\Inertia;

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

        return Inertia::render('Accounting/VATSummary/Index', [
            'period' => [
                'id' => $period->id,
                'name' => $period->name,
                'type' => $period->type,
                'status' => $period->status,
                'vat_file' => $period->vat_file,
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
        ]);
    }

    public function uploadVatFile(Request $request, AccountingPeriod $period)
    {
        $request->validate([
            'vat_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        // Delete old file if exists
        if ($period->vat_file && \Storage::disk('public')->exists($period->vat_file)) {
            \Storage::disk('public')->delete($period->vat_file);
        }

        // Store new file
        $path = $request->file('vat_file')->store('vat_files', 'public');
        $period->update(['vat_file' => $path]);

        return redirect()->back()->with('success', 'VAT file uploaded successfully.');
    }

    public function deleteVatFile(AccountingPeriod $period)
    {
        if ($period->vat_file && \Storage::disk('public')->exists($period->vat_file)) {
            \Storage::disk('public')->delete($period->vat_file);
        }

        $period->update(['vat_file' => null]);

        return redirect()->back()->with('success', 'VAT file deleted successfully.');
    }
}
