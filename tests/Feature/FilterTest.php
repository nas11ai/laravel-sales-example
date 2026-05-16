<?php

use App\Enums\SaleStatus;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use Tests\Feature\Helpers\WithRoles;

uses(WithRoles::class);

beforeEach(function () {
    test()->createPermissions([
        'sales.view',
        'payments.view',
    ]);
});

describe('sales index filters', function () {

    test('sales index filter by start date excludes earlier sales', function () {
        $user = test()->createUserWithPermissions([
            'sales.view',
        ]);

        Sale::factory()->create([
            'tanggal' => '2026-04-01',
            'status'  => SaleStatus::UNPAID,
        ]);

        Sale::factory()->create([
            'tanggal' => '2026-05-10',
            'status'  => SaleStatus::UNPAID,
        ]);

        Sale::factory()->create([
            'tanggal' => '2026-05-20',
            'status'  => SaleStatus::UNPAID,
        ]);

        $response = $this->actingAs($user)
            ->get(route('sales.index', [
                'start_date' => '2026-05-01',
            ]));

        $response->assertInertia(
            fn($page) => $page
                ->component('sales/Index')
                ->where('sales', fn($sales) => count($sales['data']) === 2)
        );
    });

    test('sales index filter by end date excludes later sales', function () {
        $user = test()->createUserWithPermissions([
            'sales.view',
        ]);

        Sale::factory()->create([
            'tanggal' => '2026-05-01',
            'status'  => SaleStatus::UNPAID,
        ]);

        Sale::factory()->create([
            'tanggal' => '2026-05-15',
            'status'  => SaleStatus::UNPAID,
        ]);

        Sale::factory()->create([
            'tanggal' => '2026-06-01',
            'status'  => SaleStatus::UNPAID,
        ]);

        $response = $this->actingAs($user)
            ->get(route('sales.index', [
                'end_date' => '2026-05-31',
            ]));

        $response->assertInertia(
            fn($page) => $page
                ->component('sales/Index')
                ->where('sales', fn($sales) => count($sales['data']) === 2)
        );
    });

    test('sales index filter by date range returns correct data', function () {
        $user = test()->createUserWithPermissions([
            'sales.view',
        ]);

        Sale::factory()->create([
            'tanggal' => '2026-04-30',
            'status'  => SaleStatus::UNPAID,
        ]);

        Sale::factory()->create([
            'tanggal' => '2026-05-10',
            'status'  => SaleStatus::UNPAID,
        ]);

        Sale::factory()->create([
            'tanggal' => '2026-05-20',
            'status'  => SaleStatus::UNPAID,
        ]);

        Sale::factory()->create([
            'tanggal' => '2026-06-01',
            'status'  => SaleStatus::UNPAID,
        ]);

        $response = $this->actingAs($user)
            ->get(route('sales.index', [
                'start_date' => '2026-05-01',
                'end_date'   => '2026-05-31',
            ]));

        $response->assertInertia(
            fn($page) => $page
                ->where('sales', fn($sales) => count($sales['data']) === 2)
        );
    });

    test('sales index returns filter values in response', function () {
        $user = test()->createUserWithPermissions([
            'sales.view',
        ]);

        $response = $this->actingAs($user)
            ->get(route('sales.index', [
                'start_date' => '2026-05-01',
                'end_date'   => '2026-05-31',
            ]));

        $response->assertInertia(
            fn($page) => $page
                ->where('filters.start_date', '2026-05-01')
                ->where('filters.end_date', '2026-05-31')
        );
    });

    test('sales index without filter returns all sales', function () {
        $user = test()->createUserWithPermissions(['sales.view']);

        Sale::factory()->count(5)->create(['status' => SaleStatus::UNPAID]);

        $response = $this->actingAs($user)->get(route('sales.index'));

        $response->assertInertia(
            fn($page) => $page
                ->where('sales', fn($sales) => count($sales['data']) === 5)
        );
    });
});

describe('payments index filters', function () {

    test('payments index filter by start date excludes earlier payments', function () {
        $user = test()->createUserWithPermissions([
            'payments.view',
        ]);

        $creator = User::factory()->create();

        $sale = Sale::create([
            'created_by'   => $creator->id,
            'kode'         => 'SL-001',
            'tanggal'      => '2026-05-01',
            'status'       => SaleStatus::UNPAID,
            'total_amount' => 300000,
        ]);

        Payment::create([
            'sale_id' => $sale->id,
            'kode'    => 'PAY-001',
            'tanggal' => '2026-04-01',
            'jumlah'  => 50000,
        ]);

        Payment::create([
            'sale_id' => $sale->id,
            'kode'    => 'PAY-002',
            'tanggal' => '2026-05-10',
            'jumlah'  => 50000,
        ]);

        Payment::create([
            'sale_id' => $sale->id,
            'kode'    => 'PAY-003',
            'tanggal' => '2026-05-20',
            'jumlah'  => 50000,
        ]);

        $response = $this->actingAs($user)
            ->get(route('payments.index', [
                'start_date' => '2026-05-01',
            ]));

        $response->assertInertia(
            fn($page) => $page
                ->component('payments/Index')
                ->where('payments', fn($payments) => count($payments['data']) === 2)
        );
    });

    test('payments index filter by date range returns correct data', function () {
        $user = test()->createUserWithPermissions([
            'payments.view',
        ]);

        $creator = User::factory()->create();

        $sale = Sale::create([
            'created_by'   => $creator->id,
            'kode'         => 'SL-001',
            'tanggal'      => '2026-05-01',
            'status'       => SaleStatus::UNPAID,
            'total_amount' => 400000,
        ]);

        Payment::create([
            'sale_id' => $sale->id,
            'kode'    => 'PAY-001',
            'tanggal' => '2026-04-30',
            'jumlah'  => 50000,
        ]);

        Payment::create([
            'sale_id' => $sale->id,
            'kode'    => 'PAY-002',
            'tanggal' => '2026-05-10',
            'jumlah'  => 50000,
        ]);

        Payment::create([
            'sale_id' => $sale->id,
            'kode'    => 'PAY-003',
            'tanggal' => '2026-05-20',
            'jumlah'  => 50000,
        ]);

        Payment::create([
            'sale_id' => $sale->id,
            'kode'    => 'PAY-004',
            'tanggal' => '2026-06-01',
            'jumlah'  => 50000,
        ]);

        $response = $this->actingAs($user)
            ->get(route('payments.index', [
                'start_date' => '2026-05-01',
                'end_date'   => '2026-05-31',
            ]));

        $response->assertInertia(
            fn($page) => $page
                ->where('payments', fn($payments) => count($payments['data']) === 2)
        );
    });

    test('payments index returns filter values in response', function () {
        $user = test()->createUserWithPermissions([
            'payments.view',
        ]);

        $response = $this->actingAs($user)
            ->get(route('payments.index', [
                'start_date' => '2026-05-01',
                'end_date'   => '2026-05-31',
            ]));

        $response->assertInertia(
            fn($page) => $page
                ->where('filters.start_date', '2026-05-01')
                ->where('filters.end_date', '2026-05-31')
        );
    });
});
