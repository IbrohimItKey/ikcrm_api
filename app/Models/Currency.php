<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'USD',
        'SUM'
    ];


    public $table = 'currency';
}
