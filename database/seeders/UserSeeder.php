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
        $adminEmail = 'zulia.tourstravelsbd@gmail.com';
        $adminPassword = 'White@123';

        // Find existing admin by role, or by known emails, or take the first user
        $admin = User::role('admin')->first()
            ?? User::whereIn('email', ['admin@example.com', 'nafiz.official@gmail.com', $adminEmail])->first()
            ?? User::first();

        if ($admin) {
            $admin->update([
                'email'    => $adminEmail,
                'name'     => 'Admin',
                'password' => bcrypt($adminPassword),
            ]);
        } else {
            $admin = User::create([
                'email'             => $adminEmail,
                'name'              => 'Admin',
                'password'          => bcrypt($adminPassword),
                'email_verified_at' => now(),
            ]);
        }

        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
