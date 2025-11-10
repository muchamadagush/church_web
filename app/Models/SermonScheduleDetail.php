<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SermonScheduleDetail extends Model
{
    protected $fillable = [
        'sermon_schedule_id',
        'church_id',
        'month',
        'start_datetime',
        'end_datetime',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    public function schedule()
    {
        return $this->belongsTo(SermonSchedule::class, 'sermon_schedule_id');
    }

    public function church()
    {
        return $this->belongsTo(Church::class);
    }
}