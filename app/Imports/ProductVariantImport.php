<?php

namespace App\Imports;


use App\Models\MenuItemVariation;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ProductVariantImport implements ToModel, WithStartRow
{
    protected $restaurantId;
    protected $menuItemId;

    public function __construct($restaurantId, $menuItemId)
    {
        $this->restaurantId = $restaurantId;
        $this->menuItemId = $menuItemId;
    }

    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row)
    {
        MenuItemVariation::create([
            'menu_item_id' => $this->menuItemId,
            'restaurant_id' => $this->restaurantId,
            'name' => $row[0],
            'price' => $row[1],
            'discount_price' => $row[2],
        ]);
    }
}
