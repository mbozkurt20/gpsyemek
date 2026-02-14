<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MenuItemOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'option_group_id',
        'restaurant_id',
        'name',
        'price'
    ];

    public function group()
    {
        return $this->belongsTo(MenuItemOptionGroup::class, 'option_group_id');
    }
}
