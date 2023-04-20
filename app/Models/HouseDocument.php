<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseDocument extends Model
{
    protected $table = 'house_document';

    protected $fillable = [
        'house_flat_id',
        'name',
        'guid',
        'ext',
        'size',
        'main_image'

    ];


    public function house(): BelongsTo
    {
        return $this->belongsTo(HouseFlat::class);
    }
}
