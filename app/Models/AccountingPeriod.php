<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingPeriod extends Model
{
    protected $fillable = [
        'name',
        'type',
        'start_date',
        'end_date',
        'status',
        'vat_file',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function incomeEntries()
    {
        return $this->hasMany(IncomeEntry::class);
    }

    public function costOfSales()
    {
        return $this->hasMany(CostOfSale::class);
    }

    public function operatingExpenses()
    {
        return $this->hasMany(OperatingExpense::class);
    }

    public function nonOperatingEntries()
    {
        return $this->hasMany(NonOperatingEntry::class);
    }

    public function taxEntries()
    {
        return $this->hasMany(TaxEntry::class);
    }

    // Calculations
    public function getTotalIncomeAttribute()
    {
        return $this->incomeEntries()->sum('amount');
    }

    public function getTotalVatAttribute()
    {
        return $this->incomeEntries()->sum('vat_amount');
    }

    public function getTotalCostOfSalesAttribute()
    {
        return $this->costOfSales()->sum('amount');
    }

    public function getGrossProfitAttribute()
    {
        return $this->total_income - $this->total_cost_of_sales;
    }

    public function getTotalOperatingExpensesAttribute()
    {
        return $this->operatingExpenses()->sum('amount');
    }

    public function getTotalOperatingExpensesVatAttribute()
    {
        return $this->operatingExpenses()->sum('vat_amount');
    }

    public function getOperatingProfitAttribute()
    {
        return $this->gross_profit - $this->total_operating_expenses - $this->total_operating_expenses_vat;
    }

    public function getNonOperatingIncomeAttribute()
    {
        return $this->nonOperatingEntries()->where('type', 'income')->sum('amount');
    }

    public function getNonOperatingExpensesAttribute()
    {
        return $this->nonOperatingEntries()->where('type', 'expense')->sum('amount');
    }

    public function getNetNonOperatingAttribute()
    {
        return $this->non_operating_income - $this->non_operating_expenses;
    }

    public function getNetProfitBeforeTaxAttribute()
    {
        return $this->operating_profit + $this->net_non_operating;
    }

    public function getTotalTaxAttribute()
    {
        return $this->taxEntries()->sum('amount');
    }

    public function getNetProfitAfterTaxAttribute()
    {
        return $this->net_profit_before_tax - $this->total_tax;
    }
}
