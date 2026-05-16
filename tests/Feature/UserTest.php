<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\Feature\Helpers\WithRoles;

uses(WithRoles::class);

beforeEach(function () {
    test()->createPermissions([
        'users.view',
        'users.create',
        'users.edit',
        'users.delete',
        'roles.view',
        'roles.edit',
    ]);

    Role::firstOrCreate([
        'name' => 'admin',
    ]);

    Role::firstOrCreate([
        'name' => 'staff',
    ]);
});

describe('index', function () {

    test('unauthenticated user cannot access users index', function () {
        $this->get(route('master.users.index'))
            ->assertRedirect(route('login'));
    });

    test('user without permission cannot access users index', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('master.users.index'))
            ->assertForbidden();
    });

    test('user with users.view can access users index', function () {
        $user = test()->createUserWithPermissions([
            'users.view',
        ]);

        $this->actingAs($user)
            ->get(route('master.users.index'))
            ->assertOk();
    });
});

describe('create', function () {

    test('user without users.create cannot access create page', function () {
        $user = test()->createUserWithPermissions([
            'users.view',
        ]);

        $this->actingAs($user)
            ->get(route('master.users.create'))
            ->assertForbidden();
    });

    test('user with users.create can access create page', function () {
        $user = test()->createUserWithPermissions([
            'users.create',
        ]);

        $this->actingAs($user)
            ->get(route('master.users.create'))
            ->assertOk();
    });
});

describe('store', function () {

    test('user with users.create can create a user', function () {
        $user = test()->createUserWithPermissions([
            'users.create',
        ]);

        $this->actingAs($user)
            ->post(route('master.users.store'), [
                'name'                  => 'John Doe',
                'email'                 => 'john@example.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'role'                  => 'staff',
            ])
            ->assertRedirect(route('master.users.index'));

        $this->assertDatabaseHas('users', [
            'name'  => 'John Doe',
            'email' => 'john@example.com',
        ]);
    });

    test('store user validates required fields', function () {
        $user = test()->createUserWithPermissions([
            'users.create',
        ]);

        $this->actingAs($user)
            ->post(route('master.users.store'), [])
            ->assertSessionHasErrors([
                'name',
                'email',
                'password',
                'role',
            ]);
    });

    test('store user validates email uniqueness', function () {
        $user = test()->createUserWithPermissions([
            'users.create',
        ]);

        User::factory()->create([
            'email' => 'existing@example.com',
        ]);

        $this->actingAs($user)
            ->post(route('master.users.store'), [
                'name'                  => 'Another User',
                'email'                 => 'existing@example.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'role'                  => 'staff',
            ])
            ->assertSessionHasErrors([
                'email',
            ]);
    });

    test('store user validates password confirmation', function () {
        $user = test()->createUserWithPermissions([
            'users.create',
        ]);

        $this->actingAs($user)
            ->post(route('master.users.store'), [
                'name'                  => 'John',
                'email'                 => 'john@example.com',
                'password'              => 'password123',
                'password_confirmation' => 'wrongpassword',
                'role'                  => 'staff',
            ])
            ->assertSessionHasErrors([
                'password',
            ]);
    });

    test('store user validates role must exist', function () {
        $user = test()->createUserWithPermissions([
            'users.create',
        ]);

        $this->actingAs($user)
            ->post(route('master.users.store'), [
                'name'                  => 'John',
                'email'                 => 'john@example.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'role'                  => 'nonexistent_role',
            ])
            ->assertSessionHasErrors([
                'role',
            ]);
    });

    test('newly created user gets assigned correct role', function () {
        $admin = test()->createUserWithPermissions([
            'users.create',
        ]);

        $this->actingAs($admin)
            ->post(route('master.users.store'), [
                'name'                  => 'Staff User',
                'email'                 => 'staff@example.com',
                'password'              => 'password123',
                'password_confirmation' => 'password123',
                'role'                  => 'staff',
            ]);

        $newUser = User::where('email', 'staff@example.com')->first();

        expect($newUser->hasRole('staff'))
            ->toBeTrue();
    });
});

describe('edit', function () {

    test('user without users.edit cannot access edit page', function () {
        $admin = test()->createUserWithPermissions([
            'users.view',
        ]);

        $target = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('master.users.edit', $target))
            ->assertForbidden();
    });

    test('user with users.edit can access edit page', function () {
        $admin = test()->createUserWithPermissions([
            'users.edit',
        ]);

        $target = User::factory()->create();

        $this->actingAs($admin)
            ->get(route('master.users.edit', $target))
            ->assertOk();
    });
});

describe('update', function () {

    test('user with users.edit can update a user', function () {
        $admin = test()->createUserWithPermissions([
            'users.edit',
        ]);

        $target = User::factory()->create([
            'name' => 'Old Name',
        ]);

        $this->actingAs($admin)
            ->put(route('master.users.update', $target), [
                'name'  => 'New Name',
                'email' => $target->email,
                'role'  => 'staff',
            ])
            ->assertRedirect(route('master.users.index'));

        expect($target->fresh()->name)
            ->toBe('New Name');
    });

    test('update user can change role', function () {
        $admin = test()->createUserWithPermissions([
            'users.edit',
        ]);

        $target = User::factory()->create();

        $target->assignRole('staff');

        $this->actingAs($admin)
            ->put(route('master.users.update', $target), [
                'name'  => $target->name,
                'email' => $target->email,
                'role'  => 'admin',
            ]);

        expect($target->fresh()->hasRole('admin'))
            ->toBeTrue()
            ->and($target->fresh()->hasRole('staff'))
            ->toBeFalse();
    });

    test('update user can change password', function () {
        $admin = test()->createUserWithPermissions([
            'users.edit',
        ]);

        $target = User::factory()->create();

        $this->actingAs($admin)
            ->put(route('master.users.update', $target), [
                'name'                  => $target->name,
                'email'                 => $target->email,
                'password'              => 'newpassword123',
                'password_confirmation' => 'newpassword123',
                'role'                  => 'staff',
            ])
            ->assertRedirect();

        expect($target->fresh()->password)
            ->not->toBe($target->password);
    });

    test('update user password is optional', function () {
        $admin = test()->createUserWithPermissions([
            'users.edit',
        ]);

        $target = User::factory()->create();

        $originalPassword = $target->password;

        $this->actingAs($admin)
            ->put(route('master.users.update', $target), [
                'name'  => $target->name,
                'email' => $target->email,
                'role'  => 'staff',
            ])
            ->assertRedirect();

        expect($target->fresh()->password)
            ->toBe($originalPassword);
    });

    test('update user validates email uniqueness ignoring own email', function () {
        $admin = test()->createUserWithPermissions([
            'users.edit',
        ]);

        $target = User::factory()->create([
            'email' => 'target@example.com',
        ]);

        $this->actingAs($admin)
            ->put(route('master.users.update', $target), [
                'name'  => $target->name,
                'email' => 'target@example.com',
                'role'  => 'staff',
            ])
            ->assertRedirect();
    });
});

describe('destroy', function () {

    test('user without users.delete cannot delete a user', function () {
        $admin = test()->createUserWithPermissions([
            'users.view',
        ]);

        $target = User::factory()->create();

        $this->actingAs($admin)
            ->delete(route('master.users.destroy', $target))
            ->assertForbidden();
    });

    test('user with users.delete can delete a user', function () {
        $admin = test()->createUserWithPermissions([
            'users.delete',
        ]);

        $target = User::factory()->create();

        $this->actingAs($admin)
            ->delete(route('master.users.destroy', $target))
            ->assertRedirect(route('master.users.index'));

        $this->assertDatabaseMissing('users', [
            'id' => $target->id,
        ]);
    });
});
