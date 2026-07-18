<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'app_name',
        'logo_path',
        'company_address',
        'company_phone',
        'company_email',
        'company_tagline',
        'footer_note',
        'letterhead_enabled',
        'letterhead_top_gap',
        'header_image_path',
        'watermark_enabled',
        'watermark_text',
        'watermark_type',
        'watermark_image_path',
    ];

    protected $casts = [
        'letterhead_enabled' => 'boolean',
        'watermark_enabled'  => 'boolean',
        'letterhead_top_gap' => 'decimal:2',
    ];

    public function getHeaderImageUrlAttribute(): ?string
    {
        if ($this->header_image_path && \Storage::disk('public')->exists($this->header_image_path)) {
            return asset('storage/' . $this->header_image_path);
        }

        return null;
    }

    public function getWatermarkImageUrlAttribute(): ?string
    {
        if ($this->watermark_image_path && \Storage::disk('public')->exists($this->watermark_image_path)) {
            return asset('storage/' . $this->watermark_image_path);
        }

        return null;
    }

    /**
     * Get the settings instance (singleton pattern)
     */
    public static function getSettings()
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'app_name' => 'Zulia Tours & Traveles BD Limited',
                'logo_path' => null,
                'company_address' => '25,26,27, Kazi Nazrul Islam Avenue, Banglamotor Trade Center, (Former Happy Rahman Plaza) 4th Floor, Banglamotor Shahbagh, Dhaka-1000.',
                'company_phone' => '+88 01716 864 109 / +88 01332 502 234',
                'company_email' => 'zulia.tourstravelsbd@gmail.com',
                'company_tagline' => 'IATA Accredited | ATAB Member | Official Quotation & Reports',
                'letterhead_enabled' => true,
                'watermark_enabled' => false,
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

        return asset('images/zulia.jpeg');
    }
}
