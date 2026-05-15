<?php

namespace Database\Seeders;

use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['kode' => 'ITM-001', 'nama' => 'Paracetamol 500mg',  'harga' => 5000],
            ['kode' => 'ITM-002', 'nama' => 'Amoxicillin 500mg',  'harga' => 15000],
            ['kode' => 'ITM-003', 'nama' => 'Vitamin C 1000mg',   'harga' => 8000],
            ['kode' => 'ITM-004', 'nama' => 'Antasida Tablet',    'harga' => 6500],
            ['kode' => 'ITM-005', 'nama' => 'Ibuprofen 400mg',    'harga' => 7000],
        ];

        foreach ($items as $item) {
            Item::firstOrCreate(['kode' => $item['kode']], $item);
        }
    }
}
