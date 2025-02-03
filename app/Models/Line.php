<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Line extends Model
{
    public function sector()
    {
        return $this->belongsTo(Sector::class);
    }
    public function routes()
    {
        return $this->hasMany(Route::class);
    }
}
