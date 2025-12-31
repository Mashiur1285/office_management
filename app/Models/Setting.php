<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'app_name',
        'logo_path',
    ];

    /**
     * Get the settings instance (singleton pattern)
     */
    public static function getSettings()
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'app_name' => 'MITT',
                'logo_path' => null,
            ]
        );
    }

    /**
     * Get logo URL with fallback to default
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo_path && \Storage::disk('public')->exists($this->logo_path)) {
            return asset('storage/' . $this->logo_path);
        }

        return asset('images/mtt-logo.png'); // Fallback to default
    }
}
