<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BasketHouseFlat extends Model
{
    protected $table = 'basket_house_flat';

    const STATUS_FREE = 0;
    const STATUS_BOOKING = 1;
    const STATUS_SOLD = 2;

    const FLAT = 1;
    const BASEMENT = 2;
    const ATTIC = 3;

    protected $fillable = [
        'basket_house_id',
        'number_of_flat',
        'floor',
        'entrance',
        'room_count',
        'price',
        'doc_number',
        'status',
        'areas',
        'additional_type',
        'basket_house_flat_id',
    ];
}
