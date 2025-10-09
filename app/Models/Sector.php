<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'slug',
        'local_id',
        'area_id'
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
    public function lines()
    {
        return $this->hasMany(Line::class);
    }

    public function routes()
    {
        return Route::whereIn('line_id', $this->lines()->pluck('id'))->get();
    }
}
