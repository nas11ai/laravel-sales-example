<?php

use App\Enums\SaleStatus;
use App\Models\Item;
use App\Models\Sale;
use App\Models\User;
use Tests\Feature\Helpers\WithRoles;

uses(WithRoles::class);

beforeEach(function () {
    test()->createPermissions([
        'sales.view',
        'sales.create',
        'sales.edit',
        'sales.delete',
    ]);

    test()->item = Item::create([
        'kode'  => 'ITM-001',
        'nama'  => 'Paracetamol 500mg',
        'harga' => 5000,
    ]);
});

describe('index', function () {

    test('unauthenticated user cannot access sales index', function () {
        $this->get(route('sales.index'))
            ->assertRedirect(route('login'));
    });

    test('user without permission cannot access sales index', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('sales.index'))
            ->assertForbidden();
    });

    test('user with sales.view can access sales index', function () {
        $user = test()->createUserWithPermissions([
            'sales.view',
        ]);

        $this->actingAs($user)
            ->get(route('sales.index'))
            ->assertOk();
    });
});

describe('create', function () {

    test('user without sales.create cannot access create page', function () {
        $user = test()->createUserWithPermissions([
            'sales.view',
        ]);

        $this->actingAs($user)
            ->get(route('sales.create'))
            ->assertForbidden();
    });

    test('user with sales.create can access create page', function () {
        $user = test()->createUserWithPermissions([
            'sales.create',
        ]);

        $this->actingAs($user)
            ->get(route('sales.create'))
            ->assertOk();
    });
});

describe('store', function () {

    test('user with sales.create can create a sale', function () {
        $user = test()->createUserWithPermissions([
            'sales.create',
        ]);

        $this->actingAs($user)
            ->post(route('sales.store'), [
                'tanggal' => '2026-05-16',
                'items'   => [
                    [
                        'item_id' => test()->item->id,
                        'qty'     => 2,
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('sales', [
            'created_by'   => $user->id,
            'total_amount' => 10000,
            'status'       => SaleStatus::UNPAID->value,
        ]);
    });

    test('store sale validates required fields', function () {
        $user = test()->createUserWithPermissions([
            'sales.create',
        ]);

        $this->actingAs($user)
            ->post(route('sales.store'), [])
            ->assertSessionHasErrors([
                'tanggal',
                'items',
            ]);
    });

    test('store sale validates items must exist', function () {
        $user = test()->createUserWithPermissions([
            'sales.create',
        ]);

        $this->actingAs($user)
            ->post(route('sales.store'), [
                'tanggal' => '2026-05-16',
                'items'   => [
                    [
                        'item_id' => 999999,
                        'qty'     => 1,
                    ],
                ],
            ])
            ->assertSessionHasErrors([
                'items.0.item_id',
            ]);
    });

    test('store sale validates qty must be at least 1', function () {
        $user = test()->createUserWithPermissions([
            'sales.create',
        ]);

        $this->actingAs($user)
            ->post(route('sales.store'), [
                'tanggal' => '2026-05-16',
                'items'   => [
                    [
                        'item_id' => test()->item->id,
                        'qty'     => 0,
                    ],
                ],
            ])
            ->assertSessionHasErrors([
                'items.0.qty',
            ]);
    });
});

describe('show', function () {

    test('user with sales.view can view a sale', function () {
        $user = test()->createUserWithPermissions([
            'sales.view',
        ]);

        $sale = Sale::factory()->create([
            'created_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('sales.show', $sale))
            ->assertOk();
    });
});

describe('edit', function () {

    test('user cannot edit a paid sale', function () {
        $user = test()->createUserWithPermissions([
            'sales.view',
            'sales.edit',
        ]);

        $sale = Sale::factory()->create([
            'created_by' => $user->id,
            'status'     => SaleStatus::PAID,
        ]);

        $this->actingAs($user)
            ->get(route('sales.edit', $sale))
            ->assertForbidden();
    });

    test('user with sales.edit can edit an unpaid sale', function () {
        $user = test()->createUserWithPermissions([
            'sales.view',
            'sales.edit',
        ]);

        $sale = Sale::factory()->create([
            'created_by' => $user->id,
            'status'     => SaleStatus::UNPAID,
        ]);

        $this->actingAs($user)
            ->get(route('sales.edit', $sale))
            ->assertOk();
    });
});

describe('update', function () {

    test('user with sales.edit can update a sale', function () {
        $user = test()->createUserWithPermissions([
            'sales.edit',
        ]);

        $sale = Sale::factory()->create([
            'created_by'   => $user->id,
            'status'       => SaleStatus::UNPAID,
            'total_amount' => 5000,
        ]);

        $item2 = Item::create([
            'kode'  => 'ITM-002',
            'nama'  => 'Vitamin C',
            'harga' => 8000,
        ]);

        $this->actingAs($user)
            ->put(route('sales.update', $sale), [
                'tanggal' => '2026-05-17',
                'items'   => [
                    [
                        'item_id' => $item2->id,
                        'qty'     => 3,
                    ],
                ],
            ])
            ->assertRedirect();

        expect($sale->fresh()->total_amount)
            ->toBe(24000);
    });
});

describe('destroy', function () {

    test('user without sales.delete cannot delete a sale', function () {
        $user = test()->createUserWithPermissions([
            'sales.view',
        ]);

        $sale = Sale::factory()->create([
            'created_by' => $user->id,
            'status'     => SaleStatus::UNPAID,
        ]);

        $this->actingAs($user)
            ->delete(route('sales.destroy', $sale))
            ->assertForbidden();
    });

    test('user with sales.delete can delete an unpaid sale', function () {
        $user = test()->createUserWithPermissions([
            'sales.delete',
        ]);

        $sale = Sale::factory()->create([
            'created_by' => $user->id,
            'status'     => SaleStatus::UNPAID,
        ]);

        $this->actingAs($user)
            ->delete(route('sales.destroy', $sale))
            ->assertRedirect(route('sales.index'));

        $this->assertDatabaseMissing('sales', [
            'id' => $sale->id,
        ]);
    });

    test('user cannot delete a paid sale', function () {
        $user = test()->createUserWithPermissions([
            'sales.delete',
        ]);

        $sale = Sale::factory()->create([
            'created_by' => $user->id,
            'status'     => SaleStatus::PAID,
        ]);

        $this->actingAs($user)
            ->delete(route('sales.destroy', $sale))
            ->assertForbidden();
    });
});
