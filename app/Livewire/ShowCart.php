<?php

namespace App\Livewire;

use App\Models\MenuItem;
use App\Models\MenuItemOption;
use App\Models\Restaurant;
use Livewire\Component;

class ShowCart extends Component
{
    public $restaurant, $menuItem, $quantity = 1, $instructions;

    /**
     * selectedOptions yapısı:
     * Radio için: [group_id => option_id]
     * Checkbox için: [group_id => [option_id => true/false]]
     */
    public $selectedOptions = [];

    protected $listeners = ['CartModal'];

    public function addItemQty()
    {
        $this->quantity++;
    }

    public function removeItemQty()
    {
        $this->quantity = max(1, $this->quantity - 1);
    }

    public function CartModal($itemID)
    {
        $this->resetFields();

        // Eager loading ile grupları ve seçenekleri çekiyoruz
        $this->menuItem = MenuItem::with(['optionGroups.options'])->find($itemID);

        if ($this->menuItem) {
            $this->restaurant = Restaurant::find($this->menuItem->restaurant_id);

            // Grupları initialize et (Checkboxlar için dizi hazırlığı)
            foreach ($this->menuItem->optionGroups as $group) {
                if ($group->type === 'checkbox') {
                    $this->selectedOptions[$group->id] = [];
                }
            }
        }
    }

    public function submit($restaurant_id, $menu_id)
    {
        if (!$this->menuItem) return;

        // 1. ZORUNLU ALAN KONTROLÜ
        foreach ($this->menuItem->optionGroups as $group) {
            if ($group->is_required) {
                $selection = $this->selectedOptions[$group->id] ?? null;
                $selectedCount = is_array($selection) ? count(array_filter($selection)) : (!empty($selection) ? 1 : 0);

                // Seçim yapılmamışsa veya checkbox dizisi boşsa
                if (is_null($selection) || (is_array($selection) && !array_filter($selection))) {
                    $this->dispatch('alert', [
                        'type' => 'error',
                        'message' => "{$group->name} " . __('frontend.required')
                    ]);
                    return;
                }

                // 2. MIN ADET KONTROLÜ (Eğer min_count > 0 ise ve seçim yetersizse)
                if ($group->min_count > 0 && $selectedCount < $group->min_count) {
                    $this->dispatch('alert', ['type' => 'error', 'message' => "{$group->name} grubundan en az {$group->min_count} seçim yapmalısınız!"]);
                    return;
                }

                // 3. MAX ADET KONTROLÜ (Seçim sınırı aşılmışsa)
                if ($group->max_count > 0 && $selectedCount > $group->max_count) {
                    $this->dispatch('alert', ['type' => 'error', 'message' => "{$group->name} grubundan en fazla {$group->max_count} seçim yapabilirsiniz!"]);
                    return;
                }
            }
        }

        session()->put('session_cart_restaurant_id', $restaurant_id);

        // 2. FİYAT HESAPLAMA (Ana Fiyat + Opsiyonlar)
        $totalPrice = (float)($this->menuItem->unit_price - $this->menuItem->discount_price);
        $appliedOptions = [];
        $selectedOptionIds = [];

        foreach ($this->selectedOptions as $groupId => $values) {
            $groupOptionIds = [];

            if (is_array($values)) {
                // Checkbox filtreleme
                foreach ($values as $id => $checked) {
                    if ($checked) $groupOptionIds[] = $id;
                }
            } elseif (!empty($values)) {
                // Radio ID'si
                $groupOptionIds[] = $values;
            }

            if (!empty($groupOptionIds)) {
                $options = MenuItemOption::whereIn('id', $groupOptionIds)->get();
                foreach ($options as $option) {
                    $appliedOptions[] = [
                        'id'    => $option->id,
                        'name'  => $option->name,
                        'price' => (float)$option->price,
                    ];
                    $selectedOptionIds[] = $option->id;
                    $totalPrice += (float)$option->price;
                }
            }
        }

        // 3. SEPET İÇİN BENZERSİZ KEY (Aynı ürün farklı opsiyonlarla eklenirse ayırmak için)
        sort($selectedOptionIds);
        $variationFingerprint = implode('-', $selectedOptionIds);

        $instructions = !blank($this->instructions) ? $this->instructions : "";

        // 4. CART DATA (Eski variationID hatasını önlemek için null geçiyoruz)
        $cartItem = [
            'id'              => $menu_id,
            'name'            => $this->menuItem->name,
            'qty'             => $this->quantity,
            'price'           => $totalPrice,
            'delivery_charge' => $this->restaurant->delivery_charge ?? 0,
            'options'         => $appliedOptions,
            'variation'       => [],
            'discount'        => (float)$this->menuItem->discount_price,
            'restaurant_id'   => $this->menuItem->restaurant_id,
            'images'          => $this->menuItem->image,
            'menuItem_id'     => $this->menuItem->id,
            'variationID'     => $variationFingerprint ?: null, // Benzersiz seçim kimliği
            'instructions'    => $instructions,
        ];

        $this->dispatch('addCart', $cartItem);
        $this->resetFields();
    }

    private function resetFields()
    {
        $this->quantity = 1;
        $this->instructions = '';
        $this->selectedOptions = [];
        $this->dispatch('closeFormModalCart');
    }

    public function render()
    {
        return view('livewire.show-cart');
    }
}
