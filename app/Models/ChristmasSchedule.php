<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChristmasSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['schedule_date', 'church_id'];
    
    protected $casts = [
        'schedule_date' => 'date',
    ];
    
    public function church()
    {
        return $this->belongsTo(Church::class);
    }
}