<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Language extends Model
{
    protected $table = 'languages';

    protected $fillable = [
        'name',
        'code',
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
