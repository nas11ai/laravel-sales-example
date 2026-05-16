<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoleResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Role::class);

        $roles = RoleResource::collection(
            Role::with('permissions')->get()
        );

        return Inertia::render('master/roles/Index', [
            'roles' => $roles,
        ]);
    }

    public function edit(Role $role): Response
    {
        Gate::authorize('update', Role::class);

        $groupedPermissions = Permission::all()
            ->groupBy(fn($p) => explode('.', $p->name)[0])
            ->map(fn($group) => $group->pluck('name')->all());

        return Inertia::render('master/roles/Edit', [
            'role' => (new RoleResource($role->loadMissing('permissions')))->resolve(),
            'groupedPermissions' => $groupedPermissions,
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        Gate::authorize('update', Role::class);

        $request->validate([
            'permissions'   => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->syncPermissions($request->permissions);

        return redirect()
            ->route('master.roles.index')
            ->with('success', "Permission role {$role->name} berhasil diperbarui.");
    }
}
