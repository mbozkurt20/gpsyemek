<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordRestCode extends Model
{
    protected $fillable = ['phone', 'code', 'expires_at'];
}
