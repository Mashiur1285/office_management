<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allPermissions = Permission::all();

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
        $admin->syncPermissions($allPermissions);

        $managerPermissions = $allPermissions->filter(fn ($permission) => ! str_contains($permission->name, '.delete'));
        $manager = Role::firstOrCreate([
            'name' => 'manager',
            'guard_name' => 'web',
        ]);
        $manager->syncPermissions($managerPermissions);

        $viewerPermissions = $allPermissions->filter(fn ($permission) => str_ends_with($permission->name, '.view'));
        $viewer = Role::firstOrCreate([
            'name' => 'viewer',
            'guard_name' => 'web',
        ]);
        $viewer->syncPermissions($viewerPermissions);
    }
}
