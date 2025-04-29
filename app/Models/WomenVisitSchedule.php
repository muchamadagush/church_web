<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WomenVisitSchedule extends Model
{
    protected $fillable = [
        'church_id',
        'visit_date',
        'worship_leader',
        'preacher'
    ];

    protected $casts = [
        'visit_date' => 'date'
    ];

    public function church()
    {
        return $this->belongsTo(Church::class);
    }
}