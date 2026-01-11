<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatingExpense extends Model
{
    protected $fillable = [
        'accounting_period_id',
        'client_id',
        'staff_id',
        'category',
        'subcategory',
        'description',
        'salary_amount',
        'bonus_amount',
        'paid_amount',
        'due_amount',
        'amount',
        'vat_amount',
        'notes',
    ];

    protected $casts = [
        'salary_amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
    ];

    public function accountingPeriod()
    {
        return $this->belongsTo(AccountingPeriod::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function staff()
    {
        return $this->belongsTo(OfficeStaff::class, 'staff_id');
    }
}
