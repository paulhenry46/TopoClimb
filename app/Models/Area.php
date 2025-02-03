<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    public function sectors()
    {
        return $this->hasMany(Sector::class);
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }
}
