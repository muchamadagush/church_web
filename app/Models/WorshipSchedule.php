<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorshipSchedule extends Model
{
    protected $fillable = [
        'title', 'description', 'type', 'schedule_date', 'location',
        'jan', 'feb', 'mar', 'apr', 'may', 'jun',
        'jul', 'aug', 'sep', 'oct', 'nov', 'dec',
        'user_id', 'church_id'
    ];

    protected $casts = [
        'schedule_date' => 'datetime',
        'jan' => 'boolean', 'feb' => 'boolean', 'mar' => 'boolean',
        'apr' => 'boolean', 'may' => 'boolean', 'jun' => 'boolean',
        'jul' => 'boolean', 'aug' => 'boolean', 'sep' => 'boolean',
        'oct' => 'boolean', 'nov' => 'boolean', 'dec' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }
}
