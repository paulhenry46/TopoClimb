<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        'route_id',
        'user_id',
        'comment',
        'video_url',
        'grade',
        'type',
        'way',
        'verified_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
