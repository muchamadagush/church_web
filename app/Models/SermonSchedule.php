<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SermonSchedule extends Model
{
    protected $fillable = [
        'pengkhotbah',
        'church_id',
        'start_datetime',
        'end_datetime',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    public function church()
    {
        return $this->belongsTo(Church::class);
    }
}