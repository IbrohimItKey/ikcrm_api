<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Chat extends Model
{
    // use HasFactory;
    protected $table = 'chat';
    protected $fillable = [
        'user_from_id',
        'user_to_id',
        'deal_id',
        'text'
    ];

    public function userTo()
    {
        return $this->belongsTo(User::class, 'user_to_id', 'id');
    }
    public function deal()
    {
        return $this->belongsTo(Deal::class);
    }
    public function userFrom()
    {
        return $this->belongsTo(User::class, 'user_from_id', 'id');
    }
}
