<?php

declare(strict_types=1);

return [
    'platform_fee_percent' => (float) env('PAYOUT_PLATFORM_FEE_PERCENT', 5.00),
    'advance_limit_percent' => (float) env('PAYOUT_ADVANCE_LIMIT_PERCENT', 40.00),
];
