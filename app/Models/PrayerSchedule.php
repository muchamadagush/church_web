<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrayerSchedule extends Model
{
    protected $fillable = [
        'tanggal',
        'nama_gereja',
        'pimpinan_pujian', 
        'pengkhotbah'
    ];

    protected $casts = [
        'tanggal' => 'date'
    ];
}