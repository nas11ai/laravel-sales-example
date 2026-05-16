<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $sales = Sale::all();

        if ($sales->isEmpty()) {
            $this->command->warn('No sales found. Run SaleSeeder first.');
            return;
        }

        // Distribusi status:
        // 40% lunas, 25% partial, 35% belum bayar
        $sales->each(function (Sale $sale, int $index) {
            $rand = $index % 20;

            if ($rand < 8) {
                // Lunas — 1 payment full
                $this->createPayment($sale, $sale->total_amount, $sale->tanggal);
            } elseif ($rand < 13) {
                // Partial — bayar sebagian (50-80%)
                $pct     = rand(50, 80) / 100;
                $bayar   = (int) round($sale->total_amount * $pct);
                $this->createPayment($sale, $bayar, $sale->tanggal);
            }
            // Sisanya (35%) tidak ada payment — status tetap UNPAID
        });

        $this->command->info('PaymentSeeder: payments created.');
    }

    private function createPayment(Sale $sale, int $jumlah, mixed $tanggal): void
    {
        DB::transaction(function () use ($sale, $jumlah, $tanggal) {
            $tanggalBayar = Carbon::parse($tanggal)
                ->addDays(rand(0, 7))
                ->format('Y-m-d');

            $kode = 'PAY-' . Carbon::parse($tanggalBayar)->format('Ymd') . '-' . str_pad($sale->id, 4, '0', STR_PAD_LEFT);

            Payment::create([
                'sale_id' => $sale->id,
                'kode'    => $kode,
                'tanggal' => $tanggalBayar,
                'jumlah'  => $jumlah,
            ]);
        });
    }
}
