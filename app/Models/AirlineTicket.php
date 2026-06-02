<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AirlineTicket extends Model
{
    protected $fillable = [
        'client_id',
        'passenger_name',
        'passenger_email',
        'passenger_phone',
        'airline_name',
        'flight_number',
        'pnr',
        'ticket_number',
        'origin',
        'destination',
        'flight_date',
        'original_flight_date',
        'departure_time',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'flight_date' => 'date',
        'original_flight_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'confirmed'  => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
            'rescheduled' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
            'cancelled'  => 'bg-red-50 text-red-700 ring-1 ring-red-200',
            'flown'      => 'bg-gray-100 text-gray-600 ring-1 ring-gray-200',
            default      => 'bg-gray-100 text-gray-600 ring-1 ring-gray-200',
        };
    }
}
