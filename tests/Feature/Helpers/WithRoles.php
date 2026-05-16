<?php

namespace Tests\Feature\Helpers;

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait WithRoles
{
    protected function createPermissions(array $permissions): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }

    protected function createAdminUser(): User
    {
        $this->createPermissions([
            'dashboard.view',
            'items.view',
            'items.create',
            'items.edit',
            'items.delete',
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'roles.view',
            'roles.edit',
            'sales.view',
            'sales.create',
            'sales.edit',
            'sales.delete',
            'payments.view',
            'payments.create',
            'payments.edit',
            'payments.delete',
        ]);

        $role = Role::firstOrCreate(['name' => 'admin']);
        $role->syncPermissions(Permission::all());

        $user = User::factory()->create();
        $user->assignRole('admin');

        return $user;
    }

    protected function createStaffUser(): User
    {
        $this->createPermissions([
            'dashboard.view',
            'items.view',
            'sales.view',
            'sales.create',
            'sales.edit',
            'sales.delete',
            'payments.view',
            'payments.create',
        ]);

        $role = Role::firstOrCreate(['name' => 'staff']);
        $role->syncPermissions([
            'dashboard.view',
            'items.view',
            'sales.view',
            'sales.create',
            'sales.edit',
            'sales.delete',
            'payments.view',
            'payments.create',
        ]);

        $user = User::factory()->create();
        $user->assignRole('staff');

        return $user;
    }

    protected function createUserWithPermissions(array $permissions): User
    {
        $this->createPermissions($permissions);

        $role = Role::firstOrCreate(['name' => 'custom_' . uniqid()]);
        $role->syncPermissions($permissions);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
