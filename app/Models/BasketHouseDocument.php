<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BasketHouseDocument extends Model
{
    protected $table = 'basket_house_document';

    protected $fillable = [
        'basket_house_flat_id',
        'name',
        'guid',
        'ext',
        'size',
        'main_image'
    ];
}
