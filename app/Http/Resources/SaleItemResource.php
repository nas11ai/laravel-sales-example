<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'item_id'        => $this->item_id,
            'item'           => $this->whenLoaded('item', fn() => [
                'id'   => $this->item->id,
                'kode' => $this->item->kode,
                'nama' => $this->item->nama,
            ]),
            'qty'            => $this->qty,
            'price_snapshot' => $this->price_snapshot,
            'total_price'    => $this->total_price,
        ];
    }
}
