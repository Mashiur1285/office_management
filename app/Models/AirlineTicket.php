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
        'passport_number',
        'airline_name',
        'flight_number',
        'pnr',
        'reservation_pnr',
        'additional_passengers',
        'ticket_number',
        'ticket_class',
        'hand_luggage_kg',
        'hand_luggage_max_weight',
        'cabin_luggage_kg',
        'cabin_luggage_max_weight',
        'complementary_food',
        'issue_date',
        'origin',
        'destination',
        'airport_name',
        'terminal',
        'gate',
        'flight_date',
        'original_flight_date',
        'departure_time',
        'arrival_date',
        'arrival_time',
        'has_transit',
        'transits',
        'status',
        'is_purchased',
        'purchase_date',
        'notes',
        'free_cancellation_days',
        'partial_cancellation_days',
        'partial_cancellation_percent',
        'no_refund_hours',
        'created_by',
    ];

    protected $casts = [
        'flight_date'          => 'date',
        'original_flight_date' => 'date',
        'issue_date'           => 'date',
        'arrival_date'         => 'date',
        'has_transit'          => 'boolean',
        'complementary_food'   => 'boolean',
        'is_purchased'         => 'boolean',
        'purchase_date'        => 'date',
        'transits'             => 'array',
        'additional_passengers' => 'array',
    ];

    /**
     * All passengers under this PNR: the primary passenger followed by any additional ones.
     *
     * @return array<int, array{passenger_name: ?string, passport_number: ?string, ticket_number: ?string}>
     */
    public function allPassengers(): array
    {
        return array_merge(
            [[
                'passenger_name'  => $this->passenger_name,
                'passport_number' => $this->passport_number,
                'ticket_number'   => $this->ticket_number,
            ]],
            $this->additional_passengers ?? []
        );
    }

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
