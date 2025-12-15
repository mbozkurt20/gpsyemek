<?php

namespace App\Observers;

use App\Models\restaurant;
use Illuminate\Support\Str;

class RestaurantObserver
{
    /**
     * Handle the restaurant "created" event.
     */
    public function creating(restaurant $restaurant): void
    {
        // Benzersiz bir API token oluştur.
        $restaurant->api_token = $this->generateUniqueToken();
    }

    public function created(Restaurant $restaurant)
    {
        // log, bildirim, event dispatch vs.
    }

    /**
     * Handle the restaurant "updated" event.
     */
    public function updated(restaurant $restaurant): void
    {
        //
    }

    /**
     * Handle the restaurant "deleted" event.
     */
    public function deleted(restaurant $restaurant): void
    {
        //
    }

    /**
     * Handle the restaurant "restored" event.
     */
    public function restored(restaurant $restaurant): void
    {
        //
    }

    /**
     * Handle the restaurant "force deleted" event.
     */
    public function forceDeleted(restaurant $restaurant): void
    {
        //
    }

    private function generateUniqueToken(): string
    {
        // İlk token'ı oluştur
        $token = Str::random(40);

        // Eğer token zaten varsa, yeni token oluşturana kadar tekrar et
        while (Restaurant::where('api_token', $token)->exists()) {
            // Token'ı tekrar oluştur
            $token = Str::random(40);
        }

        return $token;
    }
}
