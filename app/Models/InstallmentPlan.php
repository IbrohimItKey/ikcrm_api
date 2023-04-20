<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class InstallmentPlan extends Model
{
    protected $table = 'installment_plan';

    protected $fillable = [
        'period',
        'percent_type',
    ];

    // public function deal(): BelongsTo
    // {
    //     return $this->belongsTo(Deal::class);
    // }

    // public function status(): HasMany
    // {
    //     return $this->hasMany(PayStatus::class);
    // }
    //    public function deal_informations(): HasMany
    //    {
    //        return $this->hasOne(PayStatus::class);
    //    }
}
