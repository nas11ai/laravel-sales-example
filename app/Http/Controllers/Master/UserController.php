<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', User::class);

        $groupedPermissions = Permission::all()
            ->groupBy(fn($permission) => explode('.', $permission->name)[0])
            ->map(fn($group) => $group->pluck('name')->values());

        return Inertia::render('master/users/Index', [
            'users' => UserResource::collection(
                User::with('roles')->latest()->get()
            ),

            'roles' => Role::with('permissions')->get()
                ->keyBy('name')
                ->map(fn($role) => [
                    'id'          => $role->id,
                    'name'        => $role->name,
                    'permissions' => $role->permissions->pluck('name')->toArray(),
                ]),

            'groupedPermissions' => $groupedPermissions,
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', User::class);

        return Inertia::render('master/users/Create', [
            'roles' => Role::pluck('name'),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        Gate::authorize('create', User::class);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => $request->password,
        ]);

        $user->assignRole($request->role);

        return redirect()
            ->route('master.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user): Response
    {
        Gate::authorize('update', $user);

        return Inertia::render('master/users/Edit', [
            'user' => UserResource::make(
                $user->load('roles')
            )->resolve(),
            'roles' => Role::pluck('name'),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        $user->update([
            'name'  => $request->name,
            'email' => $request->email,
            ...($request->filled('password') ? ['password' => $request->password] : []),
        ]);

        $user->syncRoles($request->role);

        return redirect()
            ->route('master.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $user->delete();

        return redirect()
            ->route('master.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
