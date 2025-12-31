<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DocumentLocationHistory extends Model
{
    protected $fillable = [
        'client_id',
        'from_holder_type',
        'from_holder_id',
        'to_holder_type',
        'to_holder_id',
        'moved_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'moved_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function fromHolder(): MorphTo
    {
        return $this->morphTo('fromHolder', 'from_holder_type', 'from_holder_id');
    }

    public function toHolder(): MorphTo
    {
        return $this->morphTo('toHolder', 'to_holder_type', 'to_holder_id');
    }

    public function getFromHolderNameAttribute(): ?string
    {
        return $this->getHolderName($this->from_holder_type, $this->fromHolder);
    }

    public function getToHolderNameAttribute(): ?string
    {
        return $this->getHolderName($this->to_holder_type, $this->toHolder);
    }

    private function getHolderName(?string $type, $holder): ?string
    {
        if (!$type) {
            return null;
        }

        return match($type) {
            'agency_user' => $holder?->name ?? 'Agency User',
            'agent' => $holder?->name ?? 'Agent',
            'bd_company' => $holder?->name ?? 'BD Company',
            'foreign_company' => $holder?->name ?? 'Foreign Company',
            'agency' => 'Agency',
            'other' => 'Other',
            default => 'Unknown',
        };
    }
}
