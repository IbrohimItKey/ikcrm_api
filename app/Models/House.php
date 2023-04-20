<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class House extends Model
{
    use SoftDeletes;

    const DESIGN = 1; // Проектирование
    const AT_THE_FOUNDATION_STAGE = 2; // На этапе фундамента
    const AT_THE_PRE_SALE_STAGE = 3; // На этапе предпродаж
    const START_OF_OFFICIAL_SALES = 4; // Старт официальных продаж

    protected $table = 'house';

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

    public function house_flat(): HasMany
    {
        return $this->hasMany(HouseFlat::class);
    }
    public function house_flat_item(): HasOne
    {
        return $this->hasOne(HouseFlat::class);
    }
    public function house_flat_free(): HasMany
    {
        return $this->hasMany(HouseFlat::class)->where('status', 0);
    }
    public function house_flat_borrowing(): HasMany
    {
        return $this->hasMany(HouseFlat::class)->where('status', 1);
    }
    public function house_flat_bought(): HasMany
    {
        return $this->hasMany(HouseFlat::class)->where('status', 2);
    }
}
