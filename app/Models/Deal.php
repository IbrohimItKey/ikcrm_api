<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Deal extends Model
{
    protected $table = 'deals';
    protected $fillable = [
        'user_id',
        'house_flat_id',
        'house_id',
        'client_id',
        'coupon_id',
        'price_sell',
        'agreement_number',
        'date_deal',
        'description',
        'status',
        'type',
        'installment_plan_id',
        'initial_fee',
        'initial_fee_date',
        'budget',
        'looking_for',
        'history',
    ];


    public function files(): HasMany
    {
        return $this->hasMany(DealsFile::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    //    public function house():BelongsTo{
    //        return $this->belongsTo(House::class,'id','house_flat_id');
    //    }
    public function house_flat(): BelongsTo
    {
        return $this->belongsTo(HouseFlat::class);
    }

    public function installmentPlan(): BelongsTo
    {
        return $this->belongsTo(InstallmentPlan::class);
    }

    public function house(): BelongsTo
    {
        return  $this->belongsTo(House::class);
    }

    public function informations(): HasOne
    {
        return $this->hasOne(PersonalInformations::class, 'deal_id', 'id');
    }

    public function personal_informations(): HasOne
    {
        return $this->hasOne(PersonalInformations::class, 'series_number', 'series_number');
    }

    public function main_image(): HasOne
    {
        return $this->hasOne(DealsFile::class)->where('main_image', 1)->limit(1);
    }

    public function plan(): HasOne
    {
        return $this->hasOne(InstallmentPlan::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Clients::class)->whereNull('deleted_at')->where('status', Constants::CLIENT_ACTIVE);
    }

    public function tasks(): HasOne
    {
        return $this->hasOne(Task::class, 'deal_id', 'id')->orderBy('task_date', 'desc')->orderBy('created_at', 'desc');
    }
}
