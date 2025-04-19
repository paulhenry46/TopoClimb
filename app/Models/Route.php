<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    public $fillable=['created_at'];

    public function gradeFormated(){
        $grades = [
            300 => '3a', 310 => '3a+', 320 => '3b', 330 => '3b+', 340 => '3c', 350 => '3c+',
            400 => '4a', 410 => '4a+', 420 => '4b', 430 => '4b+', 440 => '4c', 450 => '4c+',
            500 => '5a', 510 => '5a+', 520 => '5b', 530 => '5b+', 540 => '5c', 550 => '5c+',
            600 => '6a', 610 => '6a+', 620 => '6b', 630 => '6b+', 640 => '6c', 650 => '6c+',
            700 => '7a', 710 => '7a+', 720 => '7b', 730 => '7b+', 740 => '7c', 750 => '7c+',
            800 => '8a', 810 => '8a+', 820 => '8b', 830 => '8b+', 840 => '8c', 850 => '8c+',
            900 => '9a', 910 => '9a+', 920 => '9b', 930 => '9b+', 940 => '9c', 950 => '9c+',
        ];

        return $grades[$this->grade] ?? null;

    }

    public function picture(){
        if(Storage::exists('photos/site-'.$this->line->sector->area->site_id.'/area-'.$this->line->sector->area_id.'/route-'.$this->id)){
            return Storage::url('photos/site-'.$this->line->sector->area->site_id.'/area-'.$this->line->sector->area_id.'/route-'.$this->id);
        }else{
            return route('empty.photo', $this->color);
        }
    }

    public function circle(){
        if(Storage::exists('photos/site-'.$this->line->sector->area->site_id.'/area-'.$this->line->sector->area_id.'/route-'.$this->id.'.svg')){
            return Storage::url('photos/site-'.$this->line->sector->area->site_id.'/area-'.$this->line->sector->area_id.'/route-'.$this->id.'.svg');
        }else{
            return null;
        }
    }
    public function logs()
    {
        return $this->hasMany(Log::class);
    }

    public function colorToHex()
{
    $colors = [
        'red' => '#ef4444',
        'blue' => '#3b82f6',
        'green' => '#10b981',
        'yellow' => '#f59e0b',
        'purple' => '#8b5cf6',
        'pink' => '#ec4899',
        'gray' => '#6b7280',
        'indigo' => '#6366f1',
        'teal' => '#14b8a6',
        'orange' => '#f97316',
        'amber' => '#fbbf24'
    ];

    return $colors[$this->color] ?? '#000000'; // Default to black if color not found
}
}
