<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function route()
    {
        return $this->belongsTo(Route::class);
    }
}
