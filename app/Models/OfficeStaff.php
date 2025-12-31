<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeStaff extends Model
{
    protected $table = 'office_staff';

    protected $fillable = [
        'name',
        'designation',
        'email',
        'mobile',
        'nid_number',
        'address',
        'joining_date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
        ];
    }
}
