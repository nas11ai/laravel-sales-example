<?php

use App\Enums\SaleStatus;
use App\Models\Item;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Item::create([
        'kode'  => 'ITM-001',
        'nama'  => 'Paracetamol 500mg',
        'harga' => 10000,
    ]);

    $user = User::factory()->create();

    test()->service = new PaymentService();

    test()->sale = Sale::create([
        'created_by'   => $user->id,
        'kode'         => 'SL-20260516-0001',
        'tanggal'      => '2026-05-16',
        'status'       => SaleStatus::UNPAID,
        'total_amount' => 100000,
    ]);
});

describe('generateKode', function () {

    test('returns correct format', function () {
        $kode = test()->service->generateKode();

        expect($kode)->toMatch('/^PAY-\d{8}-\d{4}$/');
    });

    test('starts from 0001 when no existing payments', function () {
        $kode = test()->service->generateKode();

        expect($kode)->toEndWith('-0001');
    });

    test('increments sequence correctly', function () {
        $prefix = 'PAY-' . now()->format('Ymd') . '-';

        Payment::create([
            'sale_id' => test()->sale->id,
            'kode'    => $prefix . '0001',
            'tanggal' => '2026-05-16',
            'jumlah'  => 10000,
        ]);

        $kode = test()->service->generateKode();

        expect($kode)->toEndWith('-0002');
    });
});

describe('create', function () {

    test('stores data correctly', function () {
        $payment = test()->service->create([
            'sale_id' => test()->sale->id,
            'tanggal' => '2026-05-16',
            'jumlah'  => 50000,
        ]);

        expect($payment->sale_id)->toBe(test()->sale->id)
            ->and($payment->jumlah)->toBe(50000)
            ->and($payment->tanggal->format('Y-m-d'))->toBe('2026-05-16');
    });

    test('generates kode automatically', function () {
        $payment = test()->service->create([
            'sale_id' => test()->sale->id,
            'tanggal' => '2026-05-16',
            'jumlah'  => 50000,
        ]);

        expect($payment->kode)->toMatch('/^PAY-\d{8}-\d{4}$/');
    });
});

describe('update', function () {

    test('changes jumlah and tanggal', function () {
        $payment = test()->service->create([
            'sale_id' => test()->sale->id,
            'tanggal' => '2026-05-16',
            'jumlah'  => 50000,
        ]);

        test()->service->update($payment, [
            'tanggal' => '2026-05-17',
            'jumlah'  => 75000,
        ]);

        $payment->refresh();

        expect($payment->jumlah)->toBe(75000)
            ->and($payment->tanggal->format('Y-m-d'))
            ->toBe('2026-05-17');
    });

    test('does not change kode', function () {
        $payment = test()->service->create([
            'sale_id' => test()->sale->id,
            'tanggal' => '2026-05-16',
            'jumlah'  => 50000,
        ]);

        $originalKode = $payment->kode;

        test()->service->update($payment, [
            'tanggal' => '2026-05-17',
            'jumlah'  => 75000,
        ]);

        expect($payment->fresh()->kode)->toBe($originalKode);
    });
});
