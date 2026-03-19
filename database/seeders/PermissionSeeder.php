<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
            'quotation' => ['*', 'view', 'add', 'update', 'delete'],
            'invoice' => ['*', 'view', 'add', 'update', 'delete'],
            'agent' => ['*', 'view', 'add', 'update', 'delete'],
            'bd-company' => ['*', 'view', 'add', 'update', 'delete'],
            'foreign-company' => ['*', 'view', 'add', 'update', 'delete'],
            'office-staff' => ['*', 'view', 'add', 'update', 'delete'],
            'expense' => ['*', 'view', 'add', 'update', 'delete'],
            'accounting' => ['*', 'view', 'add', 'update', 'delete'],
            'reports' => ['*', 'view', 'refund-report'],
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

        // Auto-assign new permissions to admin role
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo('reports.refund-report');
        }
    }
}
