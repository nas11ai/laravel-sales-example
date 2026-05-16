<?php

namespace Database\Seeders;

use App\Enums\SaleStatus;
use App\Models\Item;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first();
        $staff = User::whereHas('roles', fn($q) => $q->where('name', 'staff'))->first();
        $items = Item::all();

        if ($items->isEmpty()) {
            $this->command->warn('No items found. Run ItemSeeder first.');
            return;
        }

        $users = collect([$admin, $staff])->filter();

        $salesData = [];
        for ($i = 0; $i < 40; $i++) {
            $tanggal = Carbon::now()
                ->subDays(rand(0, 180))
                ->format('Y-m-d');

            $selectedItems = $items->random(rand(1, min(4, $items->count())));
            $totalAmount   = 0;
            $itemLines     = [];

            foreach ($selectedItems as $item) {
                $qty         = rand(1, 10);
                $totalPrice  = $item->harga * $qty;
                $totalAmount += $totalPrice;

                $itemLines[] = [
                    'item_id'        => $item->id,
                    'qty'            => $qty,
                    'price_snapshot' => $item->harga,
                    'total_price'    => $totalPrice,
                ];
            }

            $salesData[] = [
                'tanggal'    => $tanggal,
                'created_by' => $users->random()->id,
                'total'      => $totalAmount,
                'items'      => $itemLines,
            ];
        }

        usort($salesData, fn($a, $b) => strcmp($a['tanggal'], $b['tanggal']));

        DB::transaction(function () use ($salesData) {
            foreach ($salesData as $index => $data) {
                $seq  = str_pad($index + 1, 4, '0', STR_PAD_LEFT);
                $kode = 'SL-' . Carbon::parse($data['tanggal'])->format('Ymd') . '-' . $seq;

                $sale = Sale::create([
                    'created_by'   => $data['created_by'],
                    'kode'         => $kode,
                    'tanggal'      => $data['tanggal'],
                    'status'       => SaleStatus::UNPAID->value,
                    'total_amount' => $data['total'],
                ]);

                foreach ($data['items'] as $line) {
                    SaleItem::create([
                        'sale_id'        => $sale->id,
                        'item_id'        => $line['item_id'],
                        'qty'            => $line['qty'],
                        'price_snapshot' => $line['price_snapshot'],
                        'total_price'    => $line['total_price'],
                        'created_at'     => $data['tanggal'],
                        'updated_at'     => $data['tanggal'],
                    ]);
                }
            }
        });

        $this->command->info('SaleSeeder: 40 sales created.');
    }
}
