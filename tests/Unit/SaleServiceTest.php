<?php

use App\Enums\SaleStatus;
use App\Models\Item;
use App\Models\Sale;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    test()->service = new SaleService();

    test()->item1 = Item::create([
        'kode'  => 'ITM-001',
        'nama'  => 'Paracetamol 500mg',
        'harga' => 5000,
    ]);

    test()->item2 = Item::create([
        'kode'  => 'ITM-002',
        'nama'  => 'Amoxicillin 500mg',
        'harga' => 15000,
    ]);
});

describe('generateKode', function () {

    test('returns correct format', function () {
        $kode = test()->service->generateKode();

        expect($kode)->toMatch('/^SL-\d{8}-\d{4}$/');
    });

    test('starts from 0001 when no existing sales', function () {
        $kode = test()->service->generateKode();

        expect($kode)->toEndWith('-0001');
    });

    test('increments sequence correctly', function () {
        $prefix = 'SL-' . now()->format('Ymd') . '-';

        Sale::factory()->create([
            'kode' => $prefix . '0001',
        ]);

        Sale::factory()->create([
            'kode' => $prefix . '0002',
        ]);

        $kode = test()->service->generateKode();

        expect($kode)->toEndWith('-0003');
    });
});

describe('create', function () {

    test('sale with single item calculates total correctly', function () {
        $user = User::factory()->create();

        $sale = test()->service->create([
            'tanggal' => '2026-05-16',
            'items'   => [
                [
                    'item_id' => test()->item1->id,
                    'qty'     => 3,
                ],
            ],
        ], $user->id);

        expect($sale->total_amount)->toBe(15000)
            ->and($sale->status)->toBe(SaleStatus::UNPAID)
            ->and($sale->created_by)->toBe($user->id);
    });

    test('sale with multiple items sums total correctly', function () {
        $user = User::factory()->create();

        $sale = test()->service->create([
            'tanggal' => '2026-05-16',
            'items'   => [
                [
                    'item_id' => test()->item1->id,
                    'qty'     => 2,
                ],
                [
                    'item_id' => test()->item2->id,
                    'qty'     => 1,
                ],
            ],
        ], $user->id);

        expect($sale->total_amount)->toBe(25000);
    });

    test('sale snapshots item price at time of sale', function () {
        $user = User::factory()->create();

        $sale = test()->service->create([
            'tanggal' => '2026-05-16',
            'items'   => [
                [
                    'item_id' => test()->item1->id,
                    'qty'     => 1,
                ],
            ],
        ], $user->id);

        test()->item1->update([
            'harga' => 99999,
        ]);

        $saleItem = $sale->items()->first();

        expect($saleItem->price_snapshot)->toBe(5000);
    });

    test('sale generates unique kode', function () {
        $user = User::factory()->create();

        $sale1 = test()->service->create([
            'tanggal' => '2026-05-16',
            'items'   => [
                [
                    'item_id' => test()->item1->id,
                    'qty'     => 1,
                ],
            ],
        ], $user->id);

        $sale2 = test()->service->create([
            'tanggal' => '2026-05-16',
            'items'   => [
                [
                    'item_id' => test()->item1->id,
                    'qty'     => 1,
                ],
            ],
        ], $user->id);

        expect($sale1->kode)->not->toBe($sale2->kode);
    });

    test('sale persists sale items to database', function () {
        $user = User::factory()->create();

        $sale = test()->service->create([
            'tanggal' => '2026-05-16',
            'items'   => [
                [
                    'item_id' => test()->item1->id,
                    'qty'     => 2,
                ],
                [
                    'item_id' => test()->item2->id,
                    'qty'     => 3,
                ],
            ],
        ], $user->id);

        expect($sale->items()->count())->toBe(2);
    });
});

describe('update', function () {

    test('recalculates total amount', function () {
        $user = User::factory()->create();

        $sale = test()->service->create([
            'tanggal' => '2026-05-16',
            'items'   => [
                [
                    'item_id' => test()->item1->id,
                    'qty'     => 1,
                ],
            ],
        ], $user->id);

        test()->service->update($sale, [
            'tanggal' => '2026-05-16',
            'items'   => [
                [
                    'item_id' => test()->item2->id,
                    'qty'     => 2,
                ],
            ],
        ]);

        expect($sale->fresh()->total_amount)->toBe(30000);
    });

    test('replaces old items with new items', function () {
        $user = User::factory()->create();

        $sale = test()->service->create([
            'tanggal' => '2026-05-16',
            'items'   => [
                [
                    'item_id' => test()->item1->id,
                    'qty'     => 1,
                ],
                [
                    'item_id' => test()->item2->id,
                    'qty'     => 1,
                ],
            ],
        ], $user->id);

        test()->service->update($sale, [
            'tanggal' => '2026-05-16',
            'items'   => [
                [
                    'item_id' => test()->item1->id,
                    'qty'     => 5,
                ],
            ],
        ]);

        expect($sale->fresh()->items()->count())->toBe(1);
    });
});
