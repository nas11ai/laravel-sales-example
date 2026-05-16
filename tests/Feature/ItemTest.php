<?php

use App\Models\Item;
use Tests\Feature\Helpers\WithRoles;

uses(WithRoles::class);

beforeEach(function () {
    test()->createPermissions([
        'items.view',
        'items.create',
        'items.edit',
        'items.delete',
    ]);
});

describe('index', function () {

    test('unauthenticated user cannot access items index', function () {
        $this->get(route('master.items.index'))
            ->assertRedirect(route('login'));
    });

    test('user with items.view can access items index', function () {
        $user = test()->createUserWithPermissions([
            'items.view',
        ]);

        $this->actingAs($user)
            ->get(route('master.items.index'))
            ->assertOk();
    });
});

describe('store', function () {

    test('user with items.create can create an item', function () {
        $user = test()->createUserWithPermissions([
            'items.create',
        ]);

        $this->actingAs($user)
            ->post(route('master.items.store'), [
                'kode'  => 'ITM-TEST',
                'nama'  => 'Test Item',
                'harga' => 10000,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('items', [
            'kode'  => 'ITM-TEST',
            'nama'  => 'Test Item',
            'harga' => 10000,
        ]);
    });

    test('store item validates required fields', function () {
        $user = test()->createUserWithPermissions([
            'items.create',
        ]);

        $this->actingAs($user)
            ->post(route('master.items.store'), [])
            ->assertSessionHasErrors([
                'kode',
                'nama',
                'harga',
            ]);
    });

    test('store item validates kode uniqueness', function () {
        $user = test()->createUserWithPermissions([
            'items.create',
        ]);

        Item::create([
            'kode'  => 'ITM-DUP',
            'nama'  => 'Original',
            'harga' => 5000,
        ]);

        $this->actingAs($user)
            ->post(route('master.items.store'), [
                'kode'  => 'ITM-DUP',
                'nama'  => 'Duplicate',
                'harga' => 5000,
            ])
            ->assertSessionHasErrors([
                'kode',
            ]);
    });

    test('store item validates harga must be positive', function () {
        $user = test()->createUserWithPermissions([
            'items.create',
        ]);

        $this->actingAs($user)
            ->post(route('master.items.store'), [
                'kode'  => 'ITM-NEG',
                'nama'  => 'Negative Price',
                'harga' => -1000,
            ])
            ->assertSessionHasErrors([
                'harga',
            ]);
    });
});

describe('update', function () {

    test('user with items.edit can update an item', function () {
        $user = test()->createUserWithPermissions([
            'items.edit',
        ]);

        $item = Item::create([
            'kode'  => 'ITM-001',
            'nama'  => 'Old Name',
            'harga' => 5000,
        ]);

        $this->actingAs($user)
            ->put(route('master.items.update', $item), [
                'kode'  => 'ITM-001',
                'nama'  => 'New Name',
                'harga' => 9000,
            ])
            ->assertRedirect();

        expect($item->fresh()->nama)
            ->toBe('New Name')
            ->and($item->fresh()->harga)
            ->toBe(9000);
    });
});

describe('destroy', function () {

    test('user with items.delete can delete an item', function () {
        $user = test()->createUserWithPermissions([
            'items.delete',
        ]);

        $item = Item::create([
            'kode'  => 'ITM-DEL',
            'nama'  => 'Delete Me',
            'harga' => 5000,
        ]);

        $this->actingAs($user)
            ->delete(route('master.items.destroy', $item))
            ->assertRedirect();

        $this->assertDatabaseMissing('items', [
            'id' => $item->id,
        ]);
    });

    test('user without items.delete cannot delete an item', function () {
        $user = test()->createUserWithPermissions([
            'items.view',
        ]);

        $item = Item::create([
            'kode'  => 'ITM-001',
            'nama'  => 'Protected',
            'harga' => 5000,
        ]);

        $this->actingAs($user)
            ->delete(route('master.items.destroy', $item))
            ->assertForbidden();
    });
});
