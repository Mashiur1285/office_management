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
        $adminEmail = 'nafiz.official@gmail.com';
        $adminPassword = 'Nafiz@123';

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
