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

    public $fillable=['created_at', 'name', 'slug', 'line_id', 'local_id', 'grade', 'color', 'comment'];

    public function defaultGradeFormated(){
        $grades = config('climb.default_cotation_reverse');

        return $grades[$this->grade] ?? null;

    }

    public function gradeFormated($grades){
        return $grades[$this->grade] ?? null;
    }

    public function picture(){
        if(Storage::exists('photos/site-'.$this->line->sector->area->site_id.'/area-'.$this->line->sector->area_id.'/route-'.$this->id)){
            return Storage::url('photos/site-'.$this->line->sector->area->site_id.'/area-'.$this->line->sector->area_id.'/route-'.$this->id);
        }else{
            return route('empty.photo', $this->color);
        }
    }

    public function filteredPicture(){
        if(Storage::exists('photos/site-'.$this->line->sector->area->site_id.'/area-'.$this->line->sector->area_id.'/route-filtered-'.$this->id)){
            return Storage::url('photos/site-'.$this->line->sector->area->site_id.'/area-'.$this->line->sector->area_id.'/route-filtered-'.$this->id);
        }else{
            return $this->picture();
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

    public function contests()
    {
        return $this->belongsToMany(Contest::class);
    }

    public function colorToHex()
{
    $colors = config('climb.colors');

    return $colors[$this->color] ?? '#000000'; // Default to black if color not found
}
}
