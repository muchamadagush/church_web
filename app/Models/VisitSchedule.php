<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitSchedule extends Model
{
    protected $fillable = [
        'church_id',
        'visit_date'
    ];

    protected $casts = [
        'visit_date' => 'date'
    ];

    public function church()
    {
        return $this->belongsTo(Church::class);
    }
}