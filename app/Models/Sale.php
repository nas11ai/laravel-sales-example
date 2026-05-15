<?php

namespace App\Models;

use App\Enums\SaleStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'created_by',
        'kode',
        'tanggal',
        'status',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'status'  => SaleStatus::class,
            'tanggal' => 'date',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function isEditable(): bool
    {
        return $this->status !== SaleStatus::PAID;
    }

    public function totalPaid(): int
    {
        return (int) $this->payments()->sum('jumlah');
    }
}
