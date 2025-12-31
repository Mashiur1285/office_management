<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForeignCompany extends Model
{
    protected $fillable = [
        'name',
        'country',
        'job_categories',
        'owner_name',
        'owner_phone',
        'office_address',
        'contact_person_name',
        'contact_person_phone',
        'per_client_fee',
    ];

    protected function casts(): array
    {
        return [
            'per_client_fee' => 'decimal:2',
        ];
    }
}
