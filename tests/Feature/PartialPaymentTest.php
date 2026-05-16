<?php

use App\Enums\SaleStatus;
use App\Models\Sale;
use App\Models\User;
use App\Services\PaymentService;
use Tests\Feature\Helpers\WithRoles;

uses(WithRoles::class);

beforeEach(function () {
    test()->createPermissions([
        'payments.create',
        'sales.edit',
    ]);

    $creator = User::factory()->create();

    test()->sale = Sale::create([
        'created_by'   => $creator->id,
        'kode'         => 'SL-20260516-0001',
        'tanggal'      => '2026-05-16',
        'status'       => SaleStatus::UNPAID,
        'total_amount' => 100000,
    ]);

    test()->service = new PaymentService();
});

describe('payment flow', function () {

    test('sale status is unpaid before any payment', function () {
        expect(test()->sale->status)
            ->toBe(SaleStatus::UNPAID);
    });

    test('sale status becomes partial after first partial payment', function () {
        test()->service->create([
            'sale_id' => test()->sale->id,
            'tanggal' => '2026-05-16',
            'jumlah'  => 40000,
        ]);

        expect(test()->sale->fresh()->status)
            ->toBe(SaleStatus::PARTIAL);
    });

    test('sale status becomes paid after full payment in multiple installments', function () {
        test()->service->create([
            'sale_id' => test()->sale->id,
            'tanggal' => '2026-05-16',
            'jumlah'  => 40000,
        ]);

        expect(test()->sale->fresh()->status)
            ->toBe(SaleStatus::PARTIAL);

        test()->service->create([
            'sale_id' => test()->sale->id,
            'tanggal' => '2026-05-17',
            'jumlah'  => 60000,
        ]);

        expect(test()->sale->fresh()->status)
            ->toBe(SaleStatus::PAID);
    });

    test('sale totalPaid returns correct sum of all payments', function () {
        test()->service->create([
            'sale_id' => test()->sale->id,
            'tanggal' => '2026-05-16',
            'jumlah'  => 30000,
        ]);

        test()->service->create([
            'sale_id' => test()->sale->id,
            'tanggal' => '2026-05-17',
            'jumlah'  => 20000,
        ]);

        expect(test()->sale->totalPaid())
            ->toBe(50000);
    });

    test('cannot store payment exceeding remaining amount via http', function () {
        $user = test()->createUserWithPermissions([
            'payments.create',
        ]);

        test()->service->create([
            'sale_id' => test()->sale->id,
            'tanggal' => '2026-05-16',
            'jumlah'  => 60000,
        ]);

        $this->actingAs($user)
            ->post(route('payments.store'), [
                'sale_id' => test()->sale->id,
                'tanggal' => '2026-05-17',
                'jumlah'  => 50000,
            ])
            ->assertSessionHasErrors([
                'jumlah',
            ]);
    });

    test('can pay remaining amount exactly', function () {
        $user = test()->createUserWithPermissions([
            'payments.create',
        ]);

        test()->service->create([
            'sale_id' => test()->sale->id,
            'tanggal' => '2026-05-16',
            'jumlah'  => 60000,
        ]);

        $this->actingAs($user)
            ->post(route('payments.store'), [
                'sale_id' => test()->sale->id,
                'tanggal' => '2026-05-17',
                'jumlah'  => 40000,
            ])
            ->assertRedirect();

        expect(test()->sale->fresh()->status)
            ->toBe(SaleStatus::PAID);
    });

    test('deleting partial payment reverts to partial or unpaid correctly', function () {
        $pay1 = test()->service->create([
            'sale_id' => test()->sale->id,
            'tanggal' => '2026-05-16',
            'jumlah'  => 40000,
        ]);

        $pay2 = test()->service->create([
            'sale_id' => test()->sale->id,
            'tanggal' => '2026-05-17',
            'jumlah'  => 40000,
        ]);

        expect(test()->sale->fresh()->status)
            ->toBe(SaleStatus::PARTIAL);

        $pay2->delete();

        expect(test()->sale->fresh()->status)
            ->toBe(SaleStatus::PARTIAL);

        $pay1->delete();

        expect(test()->sale->fresh()->status)
            ->toBe(SaleStatus::UNPAID);
    });
});

describe('sale editability', function () {

    test('sale with partial status is still editable', function () {
        test()->sale->update([
            'status' => SaleStatus::PARTIAL,
        ]);

        expect(test()->sale->isEditable())
            ->toBeTrue();
    });

    test('sale with paid status is not editable', function () {
        test()->sale->update([
            'status' => SaleStatus::PAID,
        ]);

        expect(test()->sale->isEditable())
            ->toBeFalse();
    });

    test('cannot edit paid sale', function () {
        $user = test()->createUserWithPermissions([
            'sales.edit',
        ]);

        test()->sale->update([
            'status' => SaleStatus::PAID,
        ]);

        $this->actingAs($user)
            ->get(route('sales.edit', test()->sale))
            ->assertForbidden();
    });
});

describe('sale status labels', function () {

    test('sale status label is correct for each status', function () {
        expect(SaleStatus::UNPAID->label())
            ->toBe('Belum Dibayar')
            ->and(SaleStatus::PARTIAL->label())
            ->toBe('Belum Dibayar Sepenuhnya')
            ->and(SaleStatus::PAID->label())
            ->toBe('Sudah Dibayar');
    });
});
