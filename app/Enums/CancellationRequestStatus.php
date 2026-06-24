<?php

namespace App\Enums;

enum CancellationRequestStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Review',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'text-amber-400 bg-amber-400/10',
            self::Approved => 'text-emerald-400 bg-emerald-400/10',
            self::Rejected => 'text-rose-400 bg-rose-400/10',
        };
    }
}
