<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class HouseFlat extends Model
{
    use SoftDeletes;

    protected $table = 'house_flat';

    const STATUS_FREE = 0;
    const STATUS_BOOKING = 1;
    const STATUS_SOLD = 2;

    protected $fillable = [
        'house_id',
        'number_of_flat',
        'floor',
        'entrance',
        'room_count',
        'price',
        'doc_number',
        'status',
        'areas',
        'ares_price',
        'additional_type',
        'house_flat_id',

        // 'mansard_area',
        // 'balcony',
        // 'status',
        // // 'date',
        // 'contract_number',
        // 'price',
        // 'price_pay_30',
        // 'price_pay_50',
        // 'terrace',
        // 'basement',
        // 'basement_price_pay_30',
        // 'basement_price_pay_50',
        // 'mansard',
        // 'currency',
        // 'additional_type',
        // 'kitchen_area',
        // 'house_flat_id',
    ];

    public function house(): BelongsTo
    {
        return  $this->belongsTo(House::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(HouseDocument::class);
    }

    public function main_image(): HasOne
    {
        return $this->hasOne(HouseDocument::class)->where('main_image', 1)->limit(1);
    }

    public function image(): HasOne
    {
        return $this->hasOne(HouseDocument::class)->limit(1);
    }

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class)->where('status', Constants::BOOKING_ACTIVE);
    }

    public function deal(): HasOne
    {
        return $this->hasOne(Deal::class);
    }
}
