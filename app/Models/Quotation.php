<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Quotation extends Model
{
    protected $fillable = [
        'quotation_no',
        'quotation_year',
        'sequence',
        'quotation_date',
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
        'quotation_maker_id',
        'created_by',
        'terms_type',
        'terms_text',
        'valid_until',
        'pdf_path',
    ];

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sl');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function quotationMaker(): BelongsTo
    {
        return $this->belongsTo(OfficeStaff::class, 'quotation_maker_id');
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
