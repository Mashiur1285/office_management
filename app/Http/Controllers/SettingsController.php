<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingsController extends Controller
{
    /**
     * Show the settings page
     */
    public function edit()
    {
        $settings = Setting::getSettings();

        return Inertia::render('Settings/Edit', [
            'settings' => [
                'id' => $settings->id,
                'app_name' => $settings->app_name,
                'logo_url' => $settings->logo_url, // Uses accessor
                'logo_path' => $settings->logo_path,
                'company_address' => $settings->company_address,
                'company_phone' => $settings->company_phone,
                'company_email' => $settings->company_email,
                'company_tagline' => $settings->company_tagline,
                'footer_note' => $settings->footer_note,
                'letterhead_enabled' => (bool) $settings->letterhead_enabled,
                'letterhead_top_gap' => (float) $settings->letterhead_top_gap,
                'header_image_url' => $settings->header_image_url,
                'watermark_enabled' => (bool) $settings->watermark_enabled,
                'watermark_text' => $settings->watermark_text,
                'watermark_type' => $settings->watermark_type ?: 'text',
                'watermark_image_url' => $settings->watermark_image_url,
            ],
        ]);
    }

    /**
     * Update settings
     */
    public function update(UpdateSettingsRequest $request)
    {
        $settings = Setting::getSettings();
        $data = $request->validated();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('settings/logo', 'public');
        }

        // Handle header (letterhead) image upload / removal
        if ($request->hasFile('header_image')) {
            if ($settings->header_image_path && Storage::disk('public')->exists($settings->header_image_path)) {
                Storage::disk('public')->delete($settings->header_image_path);
            }
            $data['header_image_path'] = $request->file('header_image')->store('settings/letterhead', 'public');
        } elseif (! empty($data['remove_header_image'])) {
            if ($settings->header_image_path && Storage::disk('public')->exists($settings->header_image_path)) {
                Storage::disk('public')->delete($settings->header_image_path);
            }
            $data['header_image_path'] = null;
        }

        // Handle watermark image upload / removal
        if ($request->hasFile('watermark_image')) {
            if ($settings->watermark_image_path && Storage::disk('public')->exists($settings->watermark_image_path)) {
                Storage::disk('public')->delete($settings->watermark_image_path);
            }
            $data['watermark_image_path'] = $request->file('watermark_image')->store('settings/watermark', 'public');
        } elseif (! empty($data['remove_watermark_image'])) {
            if ($settings->watermark_image_path && Storage::disk('public')->exists($settings->watermark_image_path)) {
                Storage::disk('public')->delete($settings->watermark_image_path);
            }
            $data['watermark_image_path'] = null;
        }

        // Remove transient fields not stored directly as columns
        unset($data['logo'], $data['header_image'], $data['watermark_image'], $data['remove_header_image'], $data['remove_watermark_image']);

        $settings->update($data);

        return redirect()
            ->route('settings.edit')
            ->with('success', 'Settings updated successfully.');
    }
}
