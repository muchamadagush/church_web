<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChristmasSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['start_datetime', 'end_datetime', 'church_id'];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    public function church()
    {
        return $this->belongsTo(Church::class);
    }
}