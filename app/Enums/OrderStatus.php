<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pembayaran',
            self::Paid => 'Lunas',
            self::Failed => 'Gagal',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'text-amber-400 bg-amber-400/10',
            self::Paid => 'text-emerald-400 bg-emerald-400/10',
            self::Failed => 'text-rose-400 bg-rose-400/10',
            self::Cancelled => 'text-slate-400 bg-slate-400/10',
        };
    }
}
