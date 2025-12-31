<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use App\Models\Role;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{
    public function index()
    {
        $perPage = request('per_page', 25);
        $search = request('name');

        $users = User::query()
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            })
            ->with('roles:id,name')
            ->orderBy('name')
            ->paginate($perPage)
            ->through(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->roles->pluck('name')->first(),
                    'is_active' => true,
                ];
            });

        return Inertia::render('ACL/Users/Index', [
            'users' => $users,
            'filters' => [
                'name' => $search,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('ACL/Users/Create', [
            'roles' => Role::query()
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        if (! empty($data['role_id'])) {
            $user->assignRole($data['role_id']);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        return Inertia::render('ACL/Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $user->roles()->pluck('id')->first(),
            ],
            'roles' => Role::query()
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => isset($data['password']) && $data['password']
                ? bcrypt($data['password'])
                : $user->password,
        ]);

        if (array_key_exists('role_id', $data)) {
            $user->syncRoles($data['role_id'] ? [$data['role_id']] : []);
        }

        return redirect()
            ->route('users.index')
            ->with('success', 'User updated successfully.');
    }
}
