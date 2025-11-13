<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RestaurantPayInformation extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'cap_address',
        'iban_no',
        'iban_name',
        'mersis_no',
    ];
}
