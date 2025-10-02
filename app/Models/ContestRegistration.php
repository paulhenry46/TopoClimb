<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContestRegistration extends Model
{
    protected $fillable = [
        'contest_id',
        'route_id',
        'user_id',
        'registered_by',
    ];

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function registrar()
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}
