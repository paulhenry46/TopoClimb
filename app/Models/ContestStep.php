<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContestStep extends Model
{
    protected $fillable = [
        'contest_id',
        'name',
        'order',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function isActive()
    {
        $now = now();
        return $this->start_time <= $now && $this->end_time >= $now;
    }

    public function isPast()
    {
        return $this->end_time < now();
    }

    public function isFuture()
    {
        return $this->start_time > now();
    }
}
