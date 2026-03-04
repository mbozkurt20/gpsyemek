<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;

class RestaurantHelper
{
    public static function getStatus($restaurant)
    {
        if (!$restaurant) {
            return 'closed';
        }

        $now = Carbon::now();
        $nowDay = strtolower($now->format('l'));
        $closedUntil = $restaurant->temporary_closed_until ? Carbon::parse($restaurant->temporary_closed_until) : null;

        if ($restaurant->permanently_closed || ($closedUntil && $closedUntil->isFuture())) {
            return 'closed';
        }

        $activeTimeSlots = $restaurant->timeSlots->where('status', 5)->where('day', $nowDay)->values();

        $isOpen = false;

        foreach ($activeTimeSlots as $slot) {
            if (isNowInTimeRange::isNowInTimeRange($slot->start_time, $slot->end_time, $now)) {
                $isOpen = true;
                break;
            }
        }

        return $isOpen ? 'open' : 'closed';
    }

    public static function getStatusMessage($restaurant): ?string
    {
        if (!$restaurant) {
            return null;
        }

        $closedUntil = $restaurant->temporary_closed_until ? Carbon::parse($restaurant->temporary_closed_until) : null;

        if ($restaurant->permanently_closed) {
            return 'Şu An Kapalı';
        }

        if ($closedUntil && $closedUntil->isFuture()) {
            return 'Süreli kapalı (' . $closedUntil->diffForHumans() . ' sonra açılacak)';
        }

        return 'Çalışma saatleri dışında';
    }
}
