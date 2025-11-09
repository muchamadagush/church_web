<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WomenVisitSchedule extends Model
{
    protected $fillable = [
        'church_id',
        'start_datetime',
        'end_datetime',
        'worship_leader',
        'preacher'
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime'
    ];

    public function church()
    {
        return $this->belongsTo(Church::class);
    }
}