<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GeoController extends Controller
{
    public function geocode(Request $request)
    {
        $address = $request->get('address');
        if (!$address) {
            return response()->json(['error' => 'No address provided'], 400);
        }

        // 1️⃣ Adresi normalize et (kısaltmaları kaldır)
        $normalized = str_replace(
            ['mah.', 'Mah.', 'sokak', 'Sokak', 'cad.', 'Cad.', 'Cd.'],
            ['mahalle', 'Mahalle', '', '', 'caddesi', 'Caddesi', 'Caddesi'],
            $address
        );

        // 2️⃣ İlk deneme
        $result = $this->fetchCoordinates($normalized);
        if ($result) return response()->json($result);

        // 3️⃣ Olmadıysa ilçe/il bazında tekrar dene
        if (preg_match('/Karaköprü/i', $address) || preg_match('/Şanlıurfa/i', $address)) {
            $fallback = "Karaköprü, Şanlıurfa, Turkey";
            $result = $this->fetchCoordinates($fallback);
            if ($result) return response()->json($result);
        }

        // 4️⃣ Hâlâ bulunamadıysa hata dön
        return response()->json(['error' => 'Address not found'], 404);
    }

    private function fetchCoordinates($query)
    {
        $response = Http::withHeaders([
            'User-Agent' => 'LaravelApp/1.0 (your_email@example.com)'
        ])->get('https://nominatim.openstreetmap.org/search', [
            'q' => $query,
            'format' => 'json',
            'limit' => 1,
            'addressdetails' => 1,
            'countrycodes' => 'tr',
        ]);

        if ($response->successful() && count($response->json()) > 0) {
            $data = $response->json()[0];
            return [
                'lat' => $data['lat'],
                'lon' => $data['lon'],
                'display_name' => $data['display_name'],
            ];
        }
        return null;
    }
}
