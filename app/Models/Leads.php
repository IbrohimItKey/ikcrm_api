<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leads extends Model
{
    const NEW_LEAD_STATUS = 1;

    use HasFactory, SoftDeletes;
    protected $table = 'leads';

    protected $fillable = [
        'name',
        'surname',
        'phone',
        'patronymic',
        'email',
        'source',
        'additional_phone',
        'series_number',
        'issued_by',
        'inn',
        'referer',
        'sent',
        'requestid',
        'lead_status_id',
        'interview_date',
        'user_id',
    ];

    public function leadStatus(): BelongsTo
    {
        return $this->belongsTo(LeadStatus::class);
    }

    protected function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
