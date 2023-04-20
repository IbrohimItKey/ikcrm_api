<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BasketHouse extends Model
{
    const DESIGN = 1; // Проектирование
    const AT_THE_FOUNDATION_STAGE = 2; // На этапе фундамента
    const AT_THE_PRE_SALE_STAGE = 3; // На этапе предпродаж
    const START_OF_OFFICIAL_SALES = 4; // Старт официальных продаж

    protected $table = 'basket_house';

    protected $fillable = [
        'name',
        'description',
        'corpus',
        'entrance_count',
        'floor_count',
        'project_stage',
        'total_flat',
        'entrance_one_floor_count',
        'has_basement',
        'has_attic',
    ];
}
