<?php

namespace App\Enums;

enum EventStatus: string
{
    case Draft = 'draft';
    case AwaitingApproval = 'awaiting_approval';
    case Published = 'published';
    case AwaitingCancellation = 'awaiting_cancellation';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::AwaitingApproval => 'Menunggu Persetujuan',
            self::Published => 'Diterbitkan',
            self::AwaitingCancellation => 'Menunggu Pembatalan',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'text-slate-400 bg-slate-400/10',
            self::AwaitingApproval => 'text-amber-400 bg-amber-400/10',
            self::Published => 'text-emerald-400 bg-emerald-400/10',
            self::AwaitingCancellation => 'text-orange-400 bg-orange-400/10',
            self::Completed => 'text-violet-400 bg-violet-400/10',
            self::Cancelled => 'text-rose-400 bg-rose-400/10',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil-square',
            self::AwaitingApproval => 'heroicon-o-clock',
            self::Published => 'heroicon-o-check-circle',
            self::AwaitingCancellation => 'heroicon-o-exclamation-triangle',
            self::Completed => 'heroicon-o-trophy',
            self::Cancelled => 'heroicon-o-x-circle',
        };
    }
}
