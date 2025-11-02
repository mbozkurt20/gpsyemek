<?php

namespace App\Imports;

use App\Enums\Status;
use App\Models\Category;
use App\Models\MenuItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

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

        // Artık return etmiyoruz çünkü zaten DB'ye yazdık
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
