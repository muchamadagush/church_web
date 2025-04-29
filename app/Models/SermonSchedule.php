<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SermonSchedule extends Model
{
    protected $fillable = ['pengkhotbah'];

    public function churches()
    {
        return $this->belongsToMany(Church::class, 'sermon_schedule_details')
                    ->withPivot('month');
    }

    public function details()
    {
        return $this->hasMany(SermonScheduleDetail::class);
    }
}