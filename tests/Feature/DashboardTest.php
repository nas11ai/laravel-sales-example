<?php

use App\Enums\SaleStatus;
use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Tests\Feature\Helpers\WithRoles;

uses(WithRoles::class);

beforeEach(function () {
    test()->createPermissions([
        'sales.view',
    ]);
});

describe('dashboard access', function () {

    test('unauthenticated user cannot access dashboard', function () {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    });

    test('user without sales view permission cannot access dashboard', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertForbidden();
    });

    test('user with sales view permission can access dashboard', function () {
        $user = test()->createUserWithPermissions([
            'sales.view',
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    });
});

describe('dashboard widgets', function () {

    test('dashboard returns correct widget data', function () {
        $user = test()->createUserWithPermissions([
            'sales.view',
        ]);

        $item = Item::create([
            'kode'  => 'ITM-001',
            'nama'  => 'Item A',
            'harga' => 10000,
        ]);

        $sale = Sale::create([
            'created_by'   => $user->id,
            'kode'         => 'SL-001',
            'tanggal'      => now()->format('Y-m-d'),
            'status'       => SaleStatus::UNPAID,
            'total_amount' => 30000,
        ]);

        SaleItem::create([
            'sale_id'        => $sale->id,
            'item_id'        => $item->id,
            'qty'            => 3,
            'price_snapshot' => 10000,
            'total_price'    => 30000,
        ]);

        $response = $this->actingAs($user)
            ->get(route('dashboard'));

        $response->assertInertia(
            fn($page) => $page
                ->component('Dashboard')
                ->has('widgets')
                ->where('widgets.total_transaksi', 1)
                ->where('widgets.total_penjualan', 30000)
                ->where('widgets.total_qty', 3)
        );
    });

    test('dashboard widgets show zero when no sales exist', function () {
        $user = test()->createUserWithPermissions([
            'sales.view',
        ]);

        $response = $this->actingAs($user)
            ->get(route('dashboard'));

        $response->assertInertia(
            fn($page) => $page
                ->where('widgets.total_transaksi', 0)
                ->where('widgets.total_penjualan', 0)
                ->where('widgets.total_qty', 0)
        );
    });
});

describe('dashboard date filters', function () {

    test('dashboard filter by date range only counts sales within range', function () {
        $user = test()->createUserWithPermissions([
            'sales.view',
        ]);

        Sale::create([
            'created_by'   => $user->id,
            'kode'         => 'SL-001',
            'tanggal'      => '2026-05-10',
            'status'       => SaleStatus::UNPAID,
            'total_amount' => 50000,
        ]);

        Sale::create([
            'created_by'   => $user->id,
            'kode'         => 'SL-002',
            'tanggal'      => '2026-03-01',
            'status'       => SaleStatus::UNPAID,
            'total_amount' => 100000,
        ]);

        $response = $this->actingAs($user)
            ->get(route('dashboard', [
                'start_date' => '2026-05-01',
                'end_date'   => '2026-05-31',
            ]));

        $response->assertInertia(
            fn($page) => $page
                ->where('widgets.total_transaksi', 1)
                ->where('widgets.total_penjualan', 50000)
        );
    });

    test('dashboard returns filter values in response', function () {
        $user = test()->createUserWithPermissions([
            'sales.view',
        ]);

        $response = $this->actingAs($user)
            ->get(route('dashboard', [
                'start_date' => '2026-05-01',
                'end_date'   => '2026-05-31',
            ]));

        $response->assertInertia(
            fn($page) => $page
                ->where('filters.start_date', '2026-05-01')
                ->where('filters.end_date', '2026-05-31')
        );
    });

    test('dashboard defaults to current month when no filter is given', function () {
        $user = test()->createUserWithPermissions([
            'sales.view',
        ]);

        $response = $this->actingAs($user)
            ->get(route('dashboard'));

        $response->assertInertia(
            fn($page) => $page
                ->where(
                    'filters.start_date',
                    now()->startOfMonth()->format('Y-m-d')
                )
                ->where(
                    'filters.end_date',
                    now()->format('Y-m-d')
                )
        );
    });
});

describe('dashboard charts', function () {

    test('dashboard returns chart data structure', function () {
        $user = test()->createUserWithPermissions([
            'sales.view',
        ]);

        $response = $this->actingAs($user)
            ->get(route('dashboard'));

        $response->assertInertia(
            fn($page) => $page
                ->has('charts.penjualan_per_bulan')
                ->has('charts.qty_per_item')
        );
    });
});
