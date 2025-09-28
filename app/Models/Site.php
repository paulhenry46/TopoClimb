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

    public function cotations($full=false){
        if($this->default_cotation == true){
            if($full){
                return config('climb.default_cotation');
            }else{
                return config('climb.default_cotation')['points'];
            }
        }else{
            if($full){
                return $this->custom_cotation;
            }else{
                return $this->custom_cotation['points'];
            }
        }
    }

    public function cotations_reverse(){
        if($this->default_cotation == true){
            $temp_cotation = config('climb.default_cotation');
        }else{
            $temp_cotation = $this->custom_cotation;
        }
        return array_flip($temp_cotation['points']);
    }

    protected $fillable = [
        'name',
        'slug',
        'address',
    ];

    public function favoritedByUsers(){

        return $this->belongsToMany(User::class);

    }

    protected $casts = [
        'custom_cotation' => 'array',
    ];
}
