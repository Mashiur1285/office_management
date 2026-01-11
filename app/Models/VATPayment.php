<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VATPayment extends Model
{
    protected $table = 'vat_payments';

    protected $fillable = [
        'accounting_period_id',
        'client_id',
        'payment_type',
        'payment_amount',
        'chalan_number',
        'chalan_slip',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'payment_amount' => 'decimal:2',
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
