<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Site extends Model
{
    public function areas()
    {
        return $this->hasMany(Area::class);
    }
    public function profile_picture(){
        if(Storage::exists('pictures/site-'.$this->id.'/profile')){
            return Storage::url('pictures/site-'.$this->id.'/profile');
        }else{
            return null;
        }
    }

    public function banner(){
        if(Storage::exists('pictures/site-'.$this->id.'/banner')){
            return Storage::url('pictures/site-'.$this->id.'/banner');
        }else{
            return null;
        }
    }

    protected $fillable = [
        'name',
        'slug',
        'adress',
    ];
}
