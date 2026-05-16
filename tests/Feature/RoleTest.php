<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Feature\Helpers\WithRoles;

uses(WithRoles::class);

beforeEach(function () {
    test()->createPermissions([
        'roles.view',
        'roles.edit',
        'sales.view',
        'sales.create',
    ]);

    test()->role = Role::firstOrCreate([
        'name' => 'staff',
    ]);
});

describe('index', function () {

    test('unauthenticated user cannot access roles index', function () {
        $this->get(route('master.roles.index'))
            ->assertRedirect(route('login'));
    });

    test('user without roles.view cannot access roles index', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('master.roles.index'))
            ->assertForbidden();
    });

    test('user with roles.view can access roles index', function () {
        $user = test()->createUserWithPermissions([
            'roles.view',
        ]);

        $this->actingAs($user)
            ->get(route('master.roles.index'))
            ->assertOk();
    });
});

describe('edit', function () {

    test('user without roles.edit cannot access role edit page', function () {
        $user = test()->createUserWithPermissions([
            'roles.view',
        ]);

        $this->actingAs($user)
            ->get(route('master.roles.edit', test()->role))
            ->assertForbidden();
    });

    test('user with roles.edit can access role edit page', function () {
        $user = test()->createUserWithPermissions([
            'roles.edit',
        ]);

        $this->actingAs($user)
            ->get(route('master.roles.edit', test()->role))
            ->assertOk();
    });
});

describe('update', function () {

    test('user with roles.edit can update role permissions', function () {
        $user = test()->createUserWithPermissions([
            'roles.edit',
        ]);

        $this->actingAs($user)
            ->put(route('master.roles.update', test()->role), [
                'permissions' => [
                    'sales.view',
                    'sales.create',
                ],
            ])
            ->assertRedirect(route('master.roles.index'));

        expect(test()->role->fresh()->hasPermissionTo('sales.view'))
            ->toBeTrue()
            ->and(test()->role->fresh()->hasPermissionTo('sales.create'))
            ->toBeTrue();
    });

    test('update role permissions replaces existing permissions', function () {
        $user = test()->createUserWithPermissions([
            'roles.edit',
        ]);

        test()->role->syncPermissions([
            'sales.view',
            'sales.create',
        ]);

        $this->actingAs($user)
            ->put(route('master.roles.update', test()->role), [
                'permissions' => [
                    'sales.view',
                ],
            ]);

        expect(test()->role->fresh()->hasPermissionTo('sales.view'))
            ->toBeTrue()
            ->and(test()->role->fresh()->hasPermissionTo('sales.create'))
            ->toBeFalse();
    });

    test('update role validates permissions must exist', function () {
        $user = test()->createUserWithPermissions([
            'roles.edit',
        ]);

        $this->actingAs($user)
            ->put(route('master.roles.update', test()->role), [
                'permissions' => [
                    'nonexistent.permission',
                ],
            ])
            ->assertSessionHasErrors([
                'permissions.0',
            ]);
    });

    test('update role requires permissions array', function () {
        $user = test()->createUserWithPermissions([
            'roles.edit',
        ]);

        $this->actingAs($user)
            ->put(route('master.roles.update', test()->role), [])
            ->assertSessionHasErrors([
                'permissions',
            ]);
    });
});
