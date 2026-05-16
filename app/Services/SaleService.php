<?php

namespace App\Services;

use App\Enums\SaleStatus;
use App\Models\Item;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function generateKode(): string
    {
        $prefix = 'SL-' . Carbon::now()->format('Ymd') . '-';
        $last   = Sale::where('kode', 'like', $prefix . '%')
            ->orderByDesc('kode')
            ->value('kode');

        $seq = $last
            ? (int) substr($last, -4) + 1
            : 1;

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function create(array $data, int $createdBy): Sale
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $totalAmount = 0;
            $itemLines   = [];

            foreach ($data['items'] as $line) {
                $item        = Item::findOrFail($line['item_id']);
                $totalPrice  = $item->harga * $line['qty'];
                $totalAmount += $totalPrice;

                $itemLines[] = [
                    'item_id'        => $item->id,
                    'qty'            => $line['qty'],
                    'price_snapshot' => $item->harga,
                    'total_price'    => $totalPrice,
                ];
            }

            $sale = Sale::create([
                'created_by'   => $createdBy,
                'kode'         => $this->generateKode(),
                'tanggal'      => $data['tanggal'],
                'status'       => SaleStatus::UNPAID,
                'total_amount' => $totalAmount,
            ]);

            $sale->items()->createMany($itemLines);

            return $sale;
        });
    }

    public function update(Sale $sale, array $data): Sale
    {
        return DB::transaction(function () use ($sale, $data) {
            $totalAmount = 0;
            $itemLines   = [];

            foreach ($data['items'] as $line) {
                $item        = Item::findOrFail($line['item_id']);
                $totalPrice  = $item->harga * $line['qty'];
                $totalAmount += $totalPrice;

                $itemLines[] = [
                    'item_id'        => $item->id,
                    'qty'            => $line['qty'],
                    'price_snapshot' => $item->harga,
                    'total_price'    => $totalPrice,
                ];
            }

            $sale->update([
                'tanggal'      => $data['tanggal'],
                'total_amount' => $totalAmount,
            ]);

            $sale->items()->delete();
            $sale->items()->createMany($itemLines);

            return $sale;
        });
    }
}
