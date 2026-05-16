<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
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
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions($permissions);

        $staff = Role::firstOrCreate(['name' => 'staff']);
        $staff->syncPermissions([
            'dashboard.view',
            'items.view',
            'sales.view',
            'sales.create',
            'sales.edit',
            'sales.delete',
            'payments.view',
            'payments.create',
        ]);
    }
}
