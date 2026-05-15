<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'kode'       => $this->kode,
            'nama'       => $this->nama,
            'harga'      => $this->harga,
            'image_path' => $this->image_path,
            'image_url'  => $this->image_path
                ? asset('storage/' . $this->image_path)
                : null,
            'created_at' => $this->created_at->format('d/m/Y'),
        ];
    }
}
