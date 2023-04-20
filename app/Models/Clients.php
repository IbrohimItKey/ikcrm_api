<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clients extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'clients';

    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'phone',
        'additional_phone',
        'email',
        'gender',
        'referer',
        'requestid',
        'status',
        'budget',
        'looking_for',
        'history',
        'source',
    ];

    // protected function users()
    // {
    //     return $this->belongsTo(User::class, 'user_id', 'id');
    // }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function informations(): HasOne
    {
        return $this->hasOne(PersonalInformations::class, 'client_id', 'id');
    }
}
