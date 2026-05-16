<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'kode'         => $this->kode,
            'tanggal'      => $this->tanggal->format('Y-m-d'),
            'tanggal_label' => $this->tanggal->format('d/m/Y'),
            'jumlah'       => $this->jumlah,
            'sale'         => $this->whenLoaded('sale', fn() => [
                'id'           => $this->sale->id,
                'kode'         => $this->sale->kode,
                'total_amount' => $this->sale->total_amount,
                'total_paid'   => $this->sale->totalPaid(),
                'status'       => $this->sale->status->value,
                'status_label' => $this->sale->status->label(),
                'sisa'         => $this->sale->total_amount - $this->sale->totalPaid(),
            ]),
            'created_at'   => $this->created_at->format('d/m/Y'),
        ];
    }
}
