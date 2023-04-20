<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusColors extends Model
{
    protected $table = 'status_colors';

    protected $fillable = [
    	'color',
    	'status',
    ];
}
