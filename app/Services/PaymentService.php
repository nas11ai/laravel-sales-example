<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function generateKode(): string
    {
        $prefix = 'PAY-' . Carbon::now()->format('Ymd') . '-';
        $last   = Payment::where('kode', 'like', $prefix . '%')
            ->orderByDesc('kode')
            ->value('kode');

        $seq = $last
            ? (int) substr($last, -4) + 1
            : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function create(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            return Payment::create([
                'sale_id' => $data['sale_id'],
                'kode'    => $this->generateKode(),
                'tanggal' => $data['tanggal'],
                'jumlah'  => $data['jumlah'],
            ]);
        });
    }

    public function update(Payment $payment, array $data): Payment
    {
        return DB::transaction(function () use ($payment, $data) {
            $payment->update([
                'tanggal' => $data['tanggal'],
                'jumlah'  => $data['jumlah'],
            ]);
            return $payment;
        });
    }
}
