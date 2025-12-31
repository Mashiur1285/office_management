<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OperatingExpense extends Model
{
    protected $fillable = [
        'accounting_period_id',
        'client_id',
        'category',
        'subcategory',
        'description',
        'amount',
        'vat_amount',
        'notes',
    ];

    protected $casts = [
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
}
