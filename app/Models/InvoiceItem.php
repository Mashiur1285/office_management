<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'sl',
        'service_description',
        'quantity',
        'unit_price',
        'discount_type',
        'discount_value',
        'discount_amount',
        'vat_rate',
        'vat_amount',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
