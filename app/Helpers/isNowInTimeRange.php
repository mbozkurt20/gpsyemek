<?php

namespace App\Helpers;

use Carbon\Carbon;

class isNowInTimeRange {
    static function isNowInTimeRange(string $start, string $end, Carbon $now): bool
    {
        $startTime = Carbon::createFromTimeString($start);
        $endTime   = Carbon::createFromTimeString($end);

        return $endTime->gt($startTime)
            ? $now->between($startTime, $endTime)
            : ($now->gte($startTime) || $now->lt($endTime));
    }
}
