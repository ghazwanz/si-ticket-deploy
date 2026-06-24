<?php

declare(strict_types=1);

namespace App\Enums;

enum PayoutType: string
{
    case Advance = 'advance';
    case Final = 'final';

    public function label(): string
    {
        return match ($this) {
            self::Advance => 'Uang Muka (Advance)',
            self::Final => 'Pelunasan (Final)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Advance => 'text-violet-600 bg-violet-50 border-violet-200 dark:text-violet-400 dark:bg-violet-500/10 dark:border-violet-500/20',
            self::Final => 'text-emerald-600 bg-emerald-50 border-emerald-200 dark:text-emerald-400 dark:bg-emerald-500/10 dark:border-emerald-500/20',
        };
    }
}
