<?php

namespace App\Http\Controllers\Acl;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Http\Requests\Acl\RoleStoreRequest;
use App\Http\Requests\Acl\RoleUpdateRequest;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $perPage = request('per_page', 25);
        $search = request('name') ?? data_get(request('filter'), 'name');

        $roles = Role::query()
            ->where('guard_name', 'web')
            ->when($search, fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
            ->with('permissions:id,name')
            ->orderBy('name')
            ->paginate($perPage)
            ->through(function (Role $role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'description' => $role->description,
                    'is_active' => $role->is_active,
                    'permissions' => $role->permissions->pluck('name'),
                ];
            });

        return Inertia::render('ACL/Roles/Index', [
            'roles' => $roles,
            'filters' => [
                'name' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('ACL/Roles/Create', [
            'permissions' => Permission::query()
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(RoleStoreRequest $request)
    {
        DB::transaction(function () use ($request) {
            $role = Role::create([
                'name' => $request->string('name'),
                'description' => $request->string('description'),
                'is_active' => $request->boolean('is_active', true),
                'guard_name' => 'web',
            ]);

            $permissionIds = $request->input('permission_ids', []);
            if (! empty($permissionIds)) {
                $role->syncPermissions($permissionIds);
            }
        });

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        abort_unless($role->guard_name === 'web', 404);

        $role->load('permissions:id,name');

        return Inertia::render('ACL/Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
                'is_active' => $role->is_active,
                'permission_ids' => $role->permissions->pluck('id'),
            ],
            'permissions' => Permission::query()
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function update(RoleUpdateRequest $request, Role $role)
    {
        abort_unless($role->guard_name === 'web', 404);

        DB::transaction(function () use ($request, $role) {
            $role->update([
                'name' => $request->string('name'),
                'description' => $request->string('description'),
                'is_active' => $request->has('is_active')
                    ? $request->boolean('is_active')
                    : $role->is_active,
            ]);

            $permissionIds = $request->input('permission_ids', []);
            $role->syncPermissions($permissionIds ?? []);
        });

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        abort_unless($role->guard_name === 'web', 404);

        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
