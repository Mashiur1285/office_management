<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminEmail = 'mefwayinternational2025@gmail.com';
        $adminPassword = 'Mefway@2025';

        $admin = User::where('email', 'admin@example.com')->first();

        if ($admin) {
            $admin->update([
                'email' => $adminEmail,
                'name' => 'Admin',
                'password' => bcrypt($adminPassword),
            ]);
        } else {
            $admin = User::updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => 'Admin',
                    'password' => bcrypt($adminPassword),
                ]
            );
        }

        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
