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
            // Delete old logo if exists
            if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
                Storage::disk('public')->delete($settings->logo_path);
            }

            // Store new logo
            $data['logo_path'] = $request->file('logo')->store('settings/logo', 'public');
        }

        // Remove logo field from data (we use logo_path)
        unset($data['logo']);

        $settings->update($data);

        return redirect()
            ->route('settings.edit')
            ->with('success', 'Settings updated successfully.');
    }
}
