<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'kode'         => $this->kode,
            'tanggal'      => $this->tanggal->format('Y-m-d'),
            'tanggal_label' => $this->tanggal->format('d/m/Y'),
            'status'       => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'total_amount' => $this->total_amount,
            'total_paid'   => $this->totalPaid(),
            'is_editable'  => $this->isEditable(),
            'created_by'   => $this->whenLoaded('createdBy', fn() => [
                'id'   => $this->createdBy->id,
                'name' => $this->createdBy->name,
            ]),
            'items'        => SaleItemResource::collection(
                $this->whenLoaded('items')
            ),
            'payments'     => $this->whenLoaded(
                'payments',
                fn() =>
                $this->payments->map(fn($p) => [
                    'id'     => $p->id,
                    'kode'   => $p->kode,
                    'jumlah' => $p->jumlah,
                    'tanggal' => $p->tanggal->format('d/m/Y'),
                ])
            ),
            'created_at'   => $this->created_at->format('d/m/Y'),
        ];
    }
}
