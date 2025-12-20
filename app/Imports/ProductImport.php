<?php

namespace App\Imports;

use App\Enums\Status;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Http;

class ProductImport implements ToModel, WithStartRow
{
    protected $restaurantId;

    public function __construct($restaurantId)
    {
        $this->restaurantId = $restaurantId;
    }

    /**
     * Başlık satırını atla (1. satır başlık)
     */
    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        // --- 1️⃣ Kategori oluştur veya mevcut olanı al ---
        $category = Category::withoutEvents(function () use ($row) {
            $category = Category::where('name', $row[0])->first();

            if (!$category) {
                $category = new Category;
                $category->name        = $row[0];
                $category->slug        = Str::slug($row[0]);
                $category->description = $row[0];
                $category->parent_id   = 0;
                $category->depth       = 0;
                $category->left        = 0;
                $category->right       = 0;
                $category->status      = 5;
                $category->requested   = 5;
                $category->save();
            }

            return $category;
        });

        // --- 2️⃣ Menü numarasını üret ---
        $existingMenuNumbers = MenuItem::where('restaurant_id', $this->restaurantId)
            ->pluck('menu_number')
            ->toArray();

        $menuNumber = $this->generateUniqueMenuNumber($existingMenuNumbers);

        // --- 3️⃣ Ürünü oluştur ---
        $menuItem = MenuItem::create([
            'restaurant_id'  => $this->restaurantId,
            'category_id'    => $category->id,
            'name'           => $row[1] ?? null,  // Ürün İsmi
            'unit_price'     => (float) $row[2] ?? 0,     // Ürün Fiyatı
            'description'    => $row[3] ?? null,  // Ürün Açıklaması
            'discount_price' => 0,
            'status'         => 5,
            'menu_number'    => $menuNumber,
        ]);

        // --- 4️⃣ Pivot tabloya ekle ---
        DB::table('category_menu_items')->insert([
            'category_id'  => $category->id,
            'menu_item_id' => $menuItem->id,
        ]);

        // 📸 IMAGE URL IMPORT
        $imageUrl = $row[4] ?? null;

        if ($imageUrl && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0',
                    'Referer'    => 'https://www.google.com/',
                ])->get($imageUrl);

                if ($response->successful()) {
                    $tempPath = storage_path('app/temp/' . uniqid() . '.jpg');

                    if (!file_exists(dirname($tempPath))) {
                        mkdir(dirname($tempPath), 0755, true);
                    }

                    file_put_contents($tempPath, $response->body());

                    $menuItem
                        ->addMedia($tempPath)
                        ->toMediaCollection('menu-items');

                    unlink($tempPath);
                }
            } catch (\Exception $e) {
                \Log::error('Image import failed', [
                    'url' => $imageUrl,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return null;
    }

    /**
     * Benzersiz menü numarası üretir
     */
    protected function generateUniqueMenuNumber(array $existingNumbers): int
    {
        do {
            $menuNumber = rand(1000, 9999);
        } while (in_array($menuNumber, $existingNumbers));

        return $menuNumber;
    }
}
