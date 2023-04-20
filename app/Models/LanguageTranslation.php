<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LanguageTranslation extends Model
{
    protected $table = 'language_translations';

    protected $fillable = [
        'name',
        'language_id',
        'lang',

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
