<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItemOptionGroup extends Model
{
    protected $table = 'menu_item_option_groups';

    protected $fillable = [
        'menu_item_id',
        'restaurant_id',
        'name',
        'type',
        'is_required',
        'min_count',
        'max_count'
    ];

    public function menuItem()
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }

    public function options()
    {
        return $this->hasMany(MenuItemOption::class, 'option_group_id');
    }

    public function variations()
    {
        return $this->hasMany(MenuItemVariation::class, 'option_group_id');
    }
}
