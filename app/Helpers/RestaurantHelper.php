<?php

namespace App\Helpers;

use Illuminate\Support\Carbon;

class RestaurantHelper
{
    public static function getStatus($restaurant)
    {
        $now = Carbon::now();
        $nowDay = strtolower($now->format('l'));
        $closedUntil = $restaurant->temporary_closed_until ? Carbon::parse($restaurant->temporary_closed_until) : null;

        // 1. Kalıcı veya Geçici Kapalı mı?
        if ($restaurant->permanently_closed || ($closedUntil && $closedUntil->isFuture())) {
            return 'closed';
        }

        // 2. Zaman Dilimi Kontrolü
        $activeTimeSlots = $restaurant->timeSlots->where('status', 5)->where('day', $nowDay)->values();

        $isOpen = false;

        if ($activeTimeSlots->isNotEmpty()) {
            foreach ($activeTimeSlots as $slot) {
                // Not: isNowInTimeRange sizin özel fonksiyonunuzdur
                if (isNowInTimeRange::isNowInTimeRange($slot->start_time, $slot->end_time, $now)) {
                    $isOpen = true;
                    break;
                }
            }
        } else {
            // Standart açılış-kapanış saati kontrolü
            $isOpen = $now->between($restaurant->opening_time, $restaurant->closing_time);
        }

        return $isOpen ? 'open' : 'closed';
    }
}
