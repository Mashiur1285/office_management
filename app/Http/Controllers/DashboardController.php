<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\BdCompany;
use App\Models\Client;
use App\Models\Expense;
use App\Models\ForeignCompany;
use App\Models\NonOperatingEntry;
use App\Models\OfficeStaff;
use App\Models\OperatingExpense;
use App\Models\DocumentLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard');
    }

    public function data(Request $request)
    {
        $now = now();
        $fileDateRange = $this->resolveDateRange(
            $request->input('file_start_date'),
            $request->input('file_end_date')
        );
        $agentDateRange = $this->resolveDateRange(
            $request->input('agent_start_date'),
            $request->input('agent_end_date')
        );
        $countryDateRange = $this->resolveDateRange(
            $request->input('country_start_date'),
            $request->input('country_end_date')
        );

        $monthWindow = collect(range(5, 0))->map(fn ($i) => $now->copy()->subMonths($i));

        $salesRaw = Client::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COALESCE(SUM(total_fee),0) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $operatingRaw = OperatingExpense::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COALESCE(SUM(amount),0) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $nonOperatingRaw = NonOperatingEntry::where('type', 'expense')
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COALESCE(SUM(amount),0) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $salesMonthly = $monthWindow->map(function ($month) use ($salesRaw) {
            $key = $month->format('Y-m');
            return [
                'label' => $month->format('M'),
                'amount' => (float) ($salesRaw[$key] ?? 0),
            ];
        })->values();

        $expensesMonthly = $monthWindow->map(function ($month) use ($operatingRaw, $nonOperatingRaw) {
            $key = $month->format('Y-m');
            return [
                'label' => $month->format('M'),
                'amount' => (float) ($operatingRaw[$key] ?? 0) + (float) ($nonOperatingRaw[$key] ?? 0),
            ];
        })->values();

        // Receivable = client advance (extra paid over total fee)
        $allReceivables = Client::query()
            ->whereNotNull('total_fee')
            ->get()
            ->map(fn ($client) => [
                'name' => $client->name,
                'amount' => max(0, (float) ($client->partial_paid_amount ?? 0) - (float) ($client->total_fee ?? 0)),
            ])
            ->filter(fn ($item) => $item['amount'] > 0)
            ->values();

        // Payable = client due amounts
        $allPayables = Client::query()
            ->whereNotNull('current_due')
            ->get()
            ->map(fn ($client) => [
                'name' => $client->name,
                'amount' => (float) ($client->current_due ?? 0),
            ])
            ->filter(fn ($item) => $item['amount'] > 0)
            ->values();

        $salesTotals = Client::selectRaw('COALESCE(SUM(total_fee),0) as total, COALESCE(SUM(current_due),0) as due')
            ->first();
        $totalSales = (float) ($salesTotals->total ?? 0);
        $totalDue = (float) ($salesTotals->due ?? 0);
        $totalPaid = max(0, $totalSales - $totalDue);
        $totalOperatingExpenses = (float) OperatingExpense::sum('amount');
        $totalNonOperatingExpenses = (float) NonOperatingEntry::where('type', 'expense')->sum('amount');
        $totalExpenses = $totalOperatingExpenses + $totalNonOperatingExpenses;

        $appUsage = Cache::remember('dashboard.app_usage.v2', 300, function () {
            $path = base_path();
            $size = 0;

            try {
                $iterator = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
                );

                foreach ($iterator as $file) {
                    if (! $file->isFile()) {
                        continue;
                    }
                    try {
                        $size += $file->getSize();
                    } catch (\Throwable $e) {
                        continue;
                    }
                }
            } catch (\Throwable $e) {
                $size = 0;
            }

            return [
                'total' => (float) $size,
                'path' => $path,
            ];
        });

        $bdCompanyBase = DocumentLocation::where('holder_type', 'bd_company')
            ->whereNull('returned_at');
        if ($fileDateRange) {
            $bdCompanyBase->whereBetween('created_at', $fileDateRange);
        }
        $bdCompanyTotal = (clone $bdCompanyBase)->count();
        $bdCompanyPending = (clone $bdCompanyBase)->where(function ($query) {
            $query->whereNull('processing_status')->orWhere('processing_status', 'pending');
        })->count();
        $bdCompanyAccepted = (clone $bdCompanyBase)->where('processing_status', 'accepted')->count();
        $bdCompanyRejected = (clone $bdCompanyBase)->where('processing_status', 'rejected')->count();
        $bdCompanyCompleted = (clone $bdCompanyBase)->where('processing_status', 'completed')->count();

        $agencyTotalQuery = Client::query();
        $agencyTotalQuery->where(function ($query) use ($fileDateRange) {
            $query->where(function ($inner) use ($fileDateRange) {
                $inner->doesntHave('documentLocation');
                if ($fileDateRange) {
                    $inner->whereBetween('clients.created_at', $fileDateRange);
                }
            })->orWhereHas('documentLocation', function ($subQuery) use ($fileDateRange) {
                $subQuery->whereIn('holder_type', ['agency', 'agency_user'])
                    ->whereNull('returned_at');
                if ($fileDateRange) {
                    $subQuery->whereBetween('created_at', $fileDateRange);
                }
            });
        });
        $agencyTotal = $agencyTotalQuery->count();

        $agentClientSummary = Agent::query()
            ->withCount(['clients' => function ($query) use ($agentDateRange) {
                if ($agentDateRange) {
                    $query->whereBetween('clients.created_at', $agentDateRange);
                }
            }])
            ->orderByDesc('clients_count')
            ->orderBy('name')
            ->get()
            ->map(fn (Agent $agent) => [
                'id' => $agent->id,
                'name' => $agent->name,
                'clients_count' => $agent->clients_count,
            ])
            ->values();

        $foreignCountrySummary = Client::query()
            ->leftJoin('foreign_companies', 'clients.foreign_company_id', '=', 'foreign_companies.id')
            ->selectRaw("COALESCE(foreign_companies.country, clients.foreign_company_country, 'Unknown') as country, COUNT(*) as total")
            ->where(function ($query) {
                $query->whereNotNull('clients.foreign_company_id')
                    ->orWhereNotNull('clients.foreign_company_country');
            })
            ->when($countryDateRange, function ($query) use ($countryDateRange) {
                $query->whereBetween('clients.created_at', $countryDateRange);
            })
            ->groupByRaw("COALESCE(foreign_companies.country, clients.foreign_company_country, 'Unknown')")
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'country' => $row->country,
                'total' => (int) $row->total,
            ])
            ->values();

        return response()->json([
            'stats' => [
                'total_clients' => Client::count(),
                'total_agents' => Agent::count(),
                'total_bd_companies' => BdCompany::count(),
                'total_foreign_companies' => ForeignCompany::count(),
                'total_staff' => OfficeStaff::count(),
            ],
            'salesMonthly' => $salesMonthly,
            'expensesMonthly' => $expensesMonthly,
            'receivableToday' => [
                'total' => $allReceivables->sum('amount'),
                'items' => $allReceivables,
            ],
            'payableToday' => [
                'total' => $allPayables->sum('amount'),
                'items' => $allPayables,
            ],
            'salesSummary' => [
                'total' => $totalSales,
                'paid' => $totalPaid,
                'due' => $totalDue,
                'expenses' => $totalExpenses,
            ],
            'appUsage' => $appUsage,
            'bdCompanyFiles' => [
                'agency_total' => $agencyTotal,
                'total' => $bdCompanyTotal,
                'pending' => $bdCompanyPending,
                'accepted' => $bdCompanyAccepted,
                'rejected' => $bdCompanyRejected,
                'completed' => $bdCompanyCompleted,
            ],
            'agentClientSummary' => $agentClientSummary,
            'foreignCountrySummary' => $foreignCountrySummary,
            'appName' => config('app.name'),
        ]);
    }

    private function resolveDateRange(?string $startDate, ?string $endDate): ?array
    {
        if (! $startDate || ! $endDate) {
            return null;
        }

        try {
            return [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }
}
