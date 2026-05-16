<?php

use App\Enums\SaleStatus;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $user = User::factory()->create();

    test()->sale = Sale::create([
        'created_by'   => $user->id,
        'kode'         => 'SL-20260516-0001',
        'tanggal'      => '2026-05-16',
        'status'       => SaleStatus::UNPAID,
        'total_amount' => 100000,
    ]);
});

describe('sale status updates', function () {

    test('becomes partial after partial payment', function () {
        Payment::create([
            'sale_id' => test()->sale->id,
            'kode'    => 'PAY-001',
            'tanggal' => '2026-05-16',
            'jumlah'  => 50000,
        ]);

        expect(test()->sale->fresh()->status)
            ->toBe(SaleStatus::PARTIAL);
    });

    test('becomes paid after full payment', function () {
        Payment::create([
            'sale_id' => test()->sale->id,
            'kode'    => 'PAY-001',
            'tanggal' => '2026-05-16',
            'jumlah'  => 100000,
        ]);

        expect(test()->sale->fresh()->status)
            ->toBe(SaleStatus::PAID);
    });

    test('reverts to unpaid after payment deleted', function () {
        $payment = Payment::create([
            'sale_id' => test()->sale->id,
            'kode'    => 'PAY-001',
            'tanggal' => '2026-05-16',
            'jumlah'  => 100000,
        ]);

        expect(test()->sale->fresh()->status)
            ->toBe(SaleStatus::PAID);

        $payment->delete();

        expect(test()->sale->fresh()->status)
            ->toBe(SaleStatus::UNPAID);
    });

    test('reverts to partial after full payment reduced', function () {
        $payment = Payment::create([
            'sale_id' => test()->sale->id,
            'kode'    => 'PAY-001',
            'tanggal' => '2026-05-16',
            'jumlah'  => 100000,
        ]);

        expect(test()->sale->fresh()->status)
            ->toBe(SaleStatus::PAID);

        $payment->update([
            'jumlah' => 50000,
        ]);

        expect(test()->sale->fresh()->status)
            ->toBe(SaleStatus::PARTIAL);
    });

    test('updates correctly with multiple payments', function () {
        Payment::create([
            'sale_id' => test()->sale->id,
            'kode'    => 'PAY-001',
            'tanggal' => '2026-05-16',
            'jumlah'  => 30000,
        ]);

        expect(test()->sale->fresh()->status)
            ->toBe(SaleStatus::PARTIAL);

        Payment::create([
            'sale_id' => test()->sale->id,
            'kode'    => 'PAY-002',
            'tanggal' => '2026-05-16',
            'jumlah'  => 70000,
        ]);

        expect(test()->sale->fresh()->status)
            ->toBe(SaleStatus::PAID);
    });
});
