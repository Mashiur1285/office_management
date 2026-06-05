<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::firstOrCreate(
            ['id' => 1],
            [
                'app_name' => 'Zulia Tours & Traveles BD Limited',
                'logo_path' => null,
            ]
        );
    }
}
