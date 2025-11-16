<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestaurantStatusMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
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

        return $next($request);
    }
}
