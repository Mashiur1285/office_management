<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomeEntry extends Model
{
    protected $fillable = [
        'accounting_period_id',
        'client_id',
        'category',
        'subcategory',
        'description',
        'amount',
        'vat_rate',
        'vat_amount',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'vat_rate' => 'decimal:2',
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

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->vat_rate > 0) {
                $model->vat_amount = ($model->amount * $model->vat_rate) / 100;
            }
        });
    }
}
