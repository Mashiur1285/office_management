<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\BdCompany;
use App\Models\Client;
use App\Models\Expense;
use App\Models\ForeignCompany;
use App\Models\OfficeStaff;
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

        $monthWindow = collect(range(5, 0))->map(fn ($i) => $now->copy()->subMonths($i));

        $salesRaw = Client::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COALESCE(SUM(total_fee),0) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $expensesRaw = Expense::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym, COALESCE(SUM(amount),0) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $salesMonthly = $monthWindow->map(function ($month) use ($salesRaw) {
            $key = $month->format('Y-m');
            return [
                'label' => $month->format('M'),
                'amount' => (float) ($salesRaw[$key] ?? 0),
            ];
        })->values();

        $expensesMonthly = $monthWindow->map(function ($month) use ($expensesRaw) {
            $key = $month->format('Y-m');
            return [
                'label' => $month->format('M'),
                'amount' => (float) ($expensesRaw[$key] ?? 0),
            ];
        })->values();

        // Total Receivable - All money to be received from clients
        $allReceivables = Client::query()
            ->whereNotNull('next_payment_amount')
            ->where('next_payment_amount', '>', 0)
            ->get()
            ->map(fn ($client) => [
                'name' => $client->name,
                'amount' => (float) ($client->next_payment_amount ?? 0),
                'due_on' => optional($client->next_payment_date)?->toDateString(),
            ]);

        // Total Payable - All unpaid expenses
        $allPayables = Expense::query()
            ->where('amount', '>', 0)
            ->get()
            ->map(fn ($expense) => [
                'name' => $expense->vendor ?? $expense->title,
                'amount' => (float) ($expense->amount ?? 0),
                'due_on' => optional($expense->paid_on)?->toDateString(),
            ]);

        $diskUsage = Cache::remember('dashboard.disk_usage', 60, function () {
            $path = base_path();
            $total = @disk_total_space($path);
            $free = @disk_free_space($path);

            if ($total === false || $free === false || $total <= 0) {
                return [
                    'total' => 0,
                    'used' => 0,
                    'free' => 0,
                    'percent' => 0,
                ];
            }

            $used = max(0, $total - $free);
            $percent = round(($used / $total) * 100, 1);

            return [
                'total' => (float) $total,
                'used' => (float) $used,
                'free' => (float) $free,
                'percent' => $percent,
            ];
        });

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
            'diskUsage' => $diskUsage,
        ]);
    }
}
