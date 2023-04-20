<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'booking';

    protected $fillable = [
        'user_id',
        'client_id',
        'house_flat_id',
        'house_id',
        'deal_id',
        'status',
        'expire_dates',
        'notification_date',
        'prepayment',
    ];


    protected static function newFactory()
    {
        // return \Modules\ForTheBuilder\Database\factories\BookingFactory::new();
    }

    protected function HouseFlat()
    {
        return $this->belongsTo(HouseFlat::class, 'house_flat_id', 'id');
    }

    protected function client()
    {
        return $this->hasOne(User::class, 'id', 'client_id');
    }
    protected function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    protected function notification()
    {
        return $this->hasOne(Notifications_::class, 'relation_id', 'id');
    }
    protected function clients()
    {
        return $this->belongsTo(Clients::class, 'client_id', 'id');
    }
}
