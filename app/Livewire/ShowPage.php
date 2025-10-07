<?php

namespace App\Livewire;

use App\Enums\MenuItemStatus;
use App\Models\MenuItem;
use Livewire\Component;
use Carbon\Carbon;

class ShowPage extends Component
{
    public $categories_products = [];
    public $categories = [];
    public $other_products = [];
    public $restaurant;
    public $menu_item;
    public $menu_id = 0;
    public $variations;
    public $options;
    public $instructions;
    public $quantity = 1;
    public $restaurant_id;
    public $activeCategory = 0;

    public function addToCartModal($itemID)
    {
        $this->dispatch('CartModal', $itemID);
        $this->dispatch('openFormModalCart');
    }

    public function setActiveCategory($id)
    {
        $this->activeCategory = $id;
    }

    public function mount()
    {
        $this->activeCategory = 'all';

        $products = MenuItem::with(['categories','media','variations','options'])
            ->where('status', MenuItemStatus::ACTIVE)
            ->where(['restaurant_id' => $this->restaurant->id])
            ->get();

        foreach($products as $key => $product) {
            $product_categories = $product->categories;
            if(!blank($product_categories)) {
                foreach($product_categories as $product_category) {
                    $this->categories[$product_category->id] = $product_category;
                    $this->categories_products[$product_category->id][$key] = $product;
                    $this->categories_products[$product_category->id][$key]['image'] = $product->image;
                }
            } else {
                $this->other_products[$key] = $product;
                $this->other_products[$key]['image'] = $product->image;
            }
        }

        return view('livewire.show-page');
    }

    public function render()
    {
        $currenttime = Carbon::now()->format('H:i:s');

        return view('livewire.show-page', compact('currenttime'));
    }
}
