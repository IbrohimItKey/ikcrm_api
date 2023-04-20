<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadComment extends Model
{
    protected $table = 'lead_comment';
    protected $fillable = [
        'user_id',
        'lead_id',
        'comment',
    ];

    public function lead():BelongsTo
    {
        return $this->belongsTo(Leads::class);
    }

    public function user():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
