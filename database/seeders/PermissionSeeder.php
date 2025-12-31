<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionMap = [
            'dashboard' => ['*', 'view'],
            'client' => ['*', 'view', 'add', 'update', 'delete'],
            'agent' => ['*', 'view', 'add', 'update'],
            'bd-company' => ['*', 'view', 'add', 'update'],
            'foreign-company' => ['*', 'view', 'add', 'update'],
            'office-staff' => ['*', 'view', 'add', 'update'],
            'expense' => ['*', 'view', 'add', 'update', 'delete'],
            'document' => ['*', 'view', 'add', 'update'],
            'job-sector' => ['*', 'add'],
            'role' => ['*', 'view', 'add', 'update', 'delete'],
            'permission' => ['*', 'view'],
            'user' => ['*', 'view', 'add', 'update'],
            'settings' => ['*', 'view', 'update'],
        ];

        foreach ($permissionMap as $module => $actions) {
            foreach ($actions as $action) {
                $name = $action === '*'
                    ? "{$module}.*"
                    : "{$module}.{$action}";

                Permission::firstOrCreate([
                    'name' => $name,
                    'guard_name' => 'web',
                ]);
            }
        }
    }
}
