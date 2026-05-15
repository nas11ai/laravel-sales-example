<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    protected $fillable = ['kode', 'nama', 'image_path', 'harga'];

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
