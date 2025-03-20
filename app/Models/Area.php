<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    protected $fillable = [
        'name',
        'slug',
        'type',
        'site_id'
    ];

    public function routes(){
        $lines_id = Line::whereIn('sector_id', $this->sectors()->pluck('id'))->pluck('id');
        return Route::whereIn('line_id', $lines_id)->get();
    }

    public function banner(){
        if(Storage::exists('pictures/site-'.$this->site->id.'/area-'.$this->id.'/picture')){
            return Storage::url('pictures/site-'.$this->site->id.'/area-'.$this->id.'/picture');
        }else{
            return null;
        }
    }
}
