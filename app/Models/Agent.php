<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agent extends Model
{
    protected $fillable = [
        'name',
        'nid_number',
        'nid_file_path',
        'district',
        'bank_details',
        'mobile',
        'address',
        'services',
    ];

    protected function casts(): array
    {
        return [
            'services' => 'array',
        ];
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }
}
