<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrayerSchedule extends Model
{
    protected $fillable = [
        'start_datetime',
        'end_datetime',
        'nama_gereja',
        'pimpinan_pujian', 
        'pengkhotbah'
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime'
    ];
}