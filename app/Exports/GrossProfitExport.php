<?php

namespace App\Exports;

use App\Models\AccountingPeriod;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GrossProfitExport implements FromCollection, WithHeadings, WithMapping
{
    protected $period;

    public function __construct(AccountingPeriod $period)
    {
        $this->period = $period;
    }

    public function collection()
    {
        // This is a summary report, so we create a collection manually
        $data = [];

        $totalIncome = $this->period->total_income;
        $totalCostOfSales = $this->period->total_cost_of_sales;
        $grossProfit = $this->period->gross_profit;
        $grossMarginPercentage = $totalIncome > 0 ? (($grossProfit / $totalIncome) * 100) : 0;

        $data[] = [
            'Metric' => 'Total Income',
            'Amount' => $totalIncome,
        ];
        $data[] = [
            'Metric' => 'Total Cost of Sales',
            'Amount' => $totalCostOfSales,
        ];
        $data[] = [
            'Metric' => 'Gross Profit',
            'Amount' => $grossProfit,
        ];
        $data[] = [
            'Metric' => 'Gross Margin',
            'Amount' => number_format($grossMarginPercentage, 2) . '%',
        ];

        return collect($data);
    }

    public function headings(): array
    {
        return [
            'Metric',
            'Amount',
        ];
    }

    public function map($row): array
    {
        return [
            $row['Metric'],
            $row['Amount'],
        ];
    }
}
