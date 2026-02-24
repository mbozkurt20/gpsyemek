<?php

namespace App\Http\Middleware;

use App\Helpers\isNowInTimeRange;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class RestaurantStatusMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        /*
        $restaurant = $request->route('restaurant'); // Route parametresinden al
        $closedUntil = $restaurant->temporary_closed_until
            ? \Carbon\Carbon::parse($restaurant->temporary_closed_until)
            : null;

        if ($restaurant->permanently_closed || ($closedUntil && $closedUntil->isFuture())) {
            return redirect()->route('home');
        } elseif ($restaurant->opening_time < now()->format('H:i:s')
            && $restaurant->closing_time > now()->format('H:i:s')) {
            $restaurantUrl = route('restaurant.show', [$restaurant]);
        } else {
            return redirect()->route('home');
        }

        // Eğer URL'yi request veya view'e taşımak istiyorsan:
        $request->merge(['restaurantUrl' => $restaurantUrl]);

        */

        $restaurant = $request->route('restaurant');

        $now = Carbon::now();
        $nowDay = strtolower($now->format('l'));

        if ($restaurant->permanently_closed || ($restaurant->temporary_closed_until && Carbon::parse($restaurant->temporary_closed_until)->isFuture())) {
            return redirect()->route('home');
        }
        $activeTimeSlots = $restaurant->timeSlots->where('status', 5)->where('day', $nowDay)->values();

        $isOpen = false;
        foreach ($activeTimeSlots as $slot) {
            if (isNowInTimeRange::isNowInTimeRange($slot->start_time, $slot->end_time, $now)) {
                $isOpen = true;
                break;
            }
        }
        if (!$isOpen) {
            return redirect()->route('home');
        }

        $request->merge(['restaurantUrl' => route('restaurant.show', $restaurant)]);

        return $next($request);
    }
}
