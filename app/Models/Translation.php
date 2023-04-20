<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Translation extends Model
{
    protected $table = 'translations';

    protected $fillable = [
        'lang',
        'lang_key',
        'lang_value'
    ];

    // public function user():BelongsTo
    // {
    //     return $this->belongsTo(User::class);
    // }

    // public function userTask():BelongsTo
    // {
    //     return $this->belongsTo(User::class,'user_task_id','id');
    // }
}
