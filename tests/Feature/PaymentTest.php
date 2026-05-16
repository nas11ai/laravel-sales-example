<?php

use App\Enums\SaleStatus;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\User;
use Tests\Feature\Helpers\WithRoles;

uses(WithRoles::class);

beforeEach(function () {
    test()->createPermissions([
        'payments.view',
        'payments.create',
        'payments.edit',
        'payments.delete',
    ]);

    $creator = User::factory()->create();

    test()->sale = Sale::create([
        'created_by'   => $creator->id,
        'kode'         => 'SL-20260516-0001',
        'tanggal'      => '2026-05-16',
        'status'       => SaleStatus::UNPAID,
        'total_amount' => 100000,
    ]);
});

describe('index', function () {

    test('unauthenticated user cannot access payments index', function () {
        $this->get(route('payments.index'))
            ->assertRedirect(route('login'));
    });

    test('user without permission cannot access payments index', function () {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('payments.index'))
            ->assertForbidden();
    });

    test('user with payments.view can access payments index', function () {
        $user = test()->createUserWithPermissions([
            'payments.view',
        ]);

        $this->actingAs($user)
            ->get(route('payments.index'))
            ->assertOk();
    });
});

describe('create', function () {

    test('user without payments.create cannot access create page', function () {
        $user = test()->createUserWithPermissions([
            'payments.view',
        ]);

        $this->actingAs($user)
            ->get(route('payments.create'))
            ->assertForbidden();
    });

    test('user with payments.create can access create page', function () {
        $user = test()->createUserWithPermissions([
            'payments.create',
        ]);

        $this->actingAs($user)
            ->get(route('payments.create'))
            ->assertOk();
    });
});

describe('store', function () {

    test('user with payments.create can create a payment', function () {
        $user = test()->createUserWithPermissions([
            'payments.create',
        ]);

        $this->actingAs($user)
            ->post(route('payments.store'), [
                'sale_id' => test()->sale->id,
                'tanggal' => '2026-05-16',
                'jumlah'  => 50000,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('payments', [
            'sale_id' => test()->sale->id,
            'jumlah'  => 50000,
        ]);
    });

    test('store payment validates required fields', function () {
        $user = test()->createUserWithPermissions([
            'payments.create',
        ]);

        $this->actingAs($user)
            ->post(route('payments.store'), [])
            ->assertSessionHasErrors([
                'sale_id',
                'tanggal',
                'jumlah',
            ]);
    });

    test('cannot create payment for non-existent sale', function () {
        $user = test()->createUserWithPermissions([
            'payments.create',
        ]);

        $this->actingAs($user)
            ->post(route('payments.store'), [
                'sale_id' => 999999,
                'tanggal' => '2026-05-16',
                'jumlah'  => 50000,
            ])
            ->assertSessionHasErrors([
                'sale_id',
            ]);
    });

    test('cannot create payment that exceeds remaining amount', function () {
        $user = test()->createUserWithPermissions([
            'payments.create',
        ]);

        $this->actingAs($user)
            ->post(route('payments.store'), [
                'sale_id' => test()->sale->id,
                'tanggal' => '2026-05-16',
                'jumlah'  => 200000,
            ])
            ->assertSessionHasErrors([
                'jumlah',
            ]);
    });

    test('cannot create payment for a fully paid sale', function () {
        $user = test()->createUserWithPermissions([
            'payments.create',
        ]);

        Payment::create([
            'sale_id' => test()->sale->id,
            'kode'    => 'PAY-001',
            'tanggal' => '2026-05-16',
            'jumlah'  => 100000,
        ]);

        $this->actingAs($user)
            ->post(route('payments.store'), [
                'sale_id' => test()->sale->id,
                'tanggal' => '2026-05-17',
                'jumlah'  => 1000,
            ])
            ->assertSessionHasErrors([
                'sale_id',
            ]);
    });
});

describe('update', function () {

    test('user with payments.edit can update a payment', function () {
        $user = test()->createUserWithPermissions([
            'payments.edit',
        ]);

        $payment = Payment::create([
            'sale_id' => test()->sale->id,
            'kode'    => 'PAY-001',
            'tanggal' => '2026-05-16',
            'jumlah'  => 50000,
        ]);

        $this->actingAs($user)
            ->put(route('payments.update', $payment), [
                'tanggal' => '2026-05-17',
                'jumlah'  => 60000,
            ])
            ->assertRedirect();

        expect($payment->fresh()->jumlah)
            ->toBe(60000);
    });

    test('cannot update payment to exceed remaining amount', function () {
        $user = test()->createUserWithPermissions([
            'payments.edit',
        ]);

        $payment = Payment::create([
            'sale_id' => test()->sale->id,
            'kode'    => 'PAY-001',
            'tanggal' => '2026-05-16',
            'jumlah'  => 50000,
        ]);

        $this->actingAs($user)
            ->put(route('payments.update', $payment), [
                'tanggal' => '2026-05-16',
                'jumlah'  => 200000,
            ])
            ->assertSessionHasErrors([
                'jumlah',
            ]);
    });
});

describe('destroy', function () {

    test('user without payments.delete cannot delete a payment', function () {
        $user = test()->createUserWithPermissions([
            'payments.view',
        ]);

        $payment = Payment::create([
            'sale_id' => test()->sale->id,
            'kode'    => 'PAY-001',
            'tanggal' => '2026-05-16',
            'jumlah'  => 50000,
        ]);

        $this->actingAs($user)
            ->delete(route('payments.destroy', $payment))
            ->assertForbidden();
    });

    test('user with payments.delete can delete a payment', function () {
        $user = test()->createUserWithPermissions([
            'payments.delete',
        ]);

        $payment = Payment::create([
            'sale_id' => test()->sale->id,
            'kode'    => 'PAY-001',
            'tanggal' => '2026-05-16',
            'jumlah'  => 50000,
        ]);

        $this->actingAs($user)
            ->delete(route('payments.destroy', $payment))
            ->assertRedirect(route('payments.index'));

        $this->assertDatabaseMissing('payments', [
            'id' => $payment->id,
        ]);
    });

    test('deleting payment recalculates sale status', function () {
        $user = test()->createUserWithPermissions([
            'payments.delete',
        ]);

        $payment = Payment::create([
            'sale_id' => test()->sale->id,
            'kode'    => 'PAY-001',
            'tanggal' => '2026-05-16',
            'jumlah'  => 100000,
        ]);

        expect(test()->sale->fresh()->status)
            ->toBe(SaleStatus::PAID);

        $this->actingAs($user)
            ->delete(route('payments.destroy', $payment));

        expect(test()->sale->fresh()->status)
            ->toBe(SaleStatus::UNPAID);
    });
});
