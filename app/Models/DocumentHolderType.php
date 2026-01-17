<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class DocumentHolderType extends Model
{
    protected $fillable = [
        'value',
        'label',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public static function labelFor(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        if (!Schema::hasTable('document_holder_types')) {
            return null;
        }

        return Cache::remember("document_holder_type_label_{$value}", 60, function () use ($value) {
            return static::query()->where('value', $value)->value('label');
        });
    }
}
