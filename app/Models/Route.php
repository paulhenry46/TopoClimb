<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
/**
 * Class Route
 *
 * Represents a climbing route in the system.
 *
 * @package App\Models
 *
 * @property int $id The unique identifier for the route.
 * @property int $line_id The foreign key referencing the associated line.
 * @property string $name The name of the route.
 * @property string $slug The slugified version of the route name.
 * @property int $local_id A local identifier for the route.
 * @property string $comment Additional comments or notes about the route.
 * @property int $grade The difficulty grade of the route.
 * @property string $color The color associated with the route.
 * @property \Illuminate\Support\Carbon|null $created_at The timestamp when the route was created.
 * @property \Illuminate\Support\Carbon|null $updated_at The timestamp when the route was last updated.
 *
 */
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

    public function registeredUser(){
         return $this->belongsToMany(User::class, 'registered_routes_users');
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

    public function thumbnail(){
        if(Storage::exists('photos/site-'.$this->line->sector->area->site_id.'/area-'.$this->line->sector->area_id.'/route-'.$this->id.'-thumbnail')){
            return Storage::url('photos/site-'.$this->line->sector->area->site_id.'/area-'.$this->line->sector->area_id.'/route-'.$this->id.'-thumbnail');
        }else{
            return $this->picture();
        }
    }

    public function qrcode(){
            return Storage::url('qrcode/site-'.$this->line->sector->area->site_id.'/area-'.$this->line->sector->area_id.'/route-'.$this->id.'.svg');
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
        'green' => '#22c55e',
        'yellow' => '#fde047',
        'purple' => '#d8b4fe',
        'pink' => '#f9a8d4',
        'gray' => '#d1d5db',
        'black' => '#000000',
        'white' => '#ffffff',
        'emerald' => '#6ee7b7',
        'orange' => '#fdba74',
        'amber' => '#fbbf24',
        'teal' => '#00bba7',
        'lime' => '#7ccf00',
        'cyan' => '#00b8db',
        'sky' => '#00a6f4',
        'indigo' => '#615fff',
        'violet' => '#8e51ff',
        'fuchsia' => '#d946ef',
        'rose' => '#f43f5e',
        'slate' => '#64748b',
        'gray' => '#6b7280',
        'zinc' => '#71717a'
    ];

    return $colors[$this->color] ?? '#000000'; // Default to black if color not found
}
}
