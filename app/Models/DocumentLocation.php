<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DocumentLocation extends Model
{
    protected $fillable = [
        'client_id',
        'holder_type',
        'holder_id',
        'processing_status',
        'processing_notes',
        'received_at',
        'expected_return_at',
        'returned_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'received_at' => 'datetime',
            'expected_return_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function holder(): MorphTo
    {
        return $this->morphTo('holder', 'holder_type', 'holder_id');
    }

    public function getHolderNameAttribute(): ?string
    {
        return match($this->holder_type) {
            'agency_user' => $this->holder?->name ?? 'Agency',
            'agent' => $this->holder?->name ?? 'Agent',
            'bd_company' => $this->holder?->name ?? 'BD Company',
            'foreign_company' => $this->holder?->name ?? 'Foreign Company',
            'agency' => 'Agency',
            'other' => 'Other',
            default => 'Unknown',
        };
    }

    public function getHolderDetailsAttribute(): array
    {
        $details = [
            'type' => $this->holder_type,
            'name' => $this->holder_name,
            'id' => $this->holder_id,
        ];

        if ($this->holder_type === 'agency_user' && $this->holder) {
            $details['email'] = $this->holder->email;
        } elseif ($this->holder_type === 'bd_company' && $this->holder) {
            $details['contact_person'] = $this->holder->contact_person_name;
            $details['phone'] = $this->holder->contact_person_phone;
        } elseif ($this->holder_type === 'foreign_company' && $this->holder) {
            $details['country'] = $this->holder->country;
            $details['phone'] = $this->holder->contact_person_phone;
        } elseif ($this->holder_type === 'agent' && $this->holder) {
            $details['mobile'] = $this->holder->mobile ?? null;
        }

        return $details;
    }
}
