<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    public function line()
    {
        return $this->belongsTo(Line::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
