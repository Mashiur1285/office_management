<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_no',
        'invoice_year',
        'sequence',
        'invoice_date',
        'client_id',
        'client_name',
        'organization_name',
        'client_mobile',
        'client_email',
        'service_category',
        'service_type',
        'description',
        'company_phone',
        'company_email',
        'company_address',
        'terms_type',
        'terms_text',
        'subtotal',
        'discount_amount',
        'vat_rate',
        'vat_amount',
        'total_amount',
        'paid_amount',
        'due_amount',
        'payment_status',
        'payment_date',
        'payment_method',
        'created_by',
        'pdf_path',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'payment_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sl');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): MorphMany
    {
        return $this->morphMany(Payment::class, 'payable');
    }
}
