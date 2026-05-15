<?php

namespace App\Enums;

enum SaleStatus: string
{
    case UNPAID  = 'belum_dibayar';
    case PARTIAL = 'belum_dibayar_sepenuhnya';
    case PAID    = 'sudah_dibayar';

    public function label(): string
    {
        return match ($this) {
            self::UNPAID   => 'Belum Dibayar',
            self::PARTIAL  => 'Belum Dibayar Sepenuhnya',
            self::PAID     => 'Sudah Dibayar',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::UNPAID   => 'destructive',
            self::PARTIAL  => 'warning',
            self::PAID     => 'success',
        };
    }
}
