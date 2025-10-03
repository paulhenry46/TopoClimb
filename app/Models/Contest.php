<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contest extends Model
{
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'mode',
        'site_id',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function routes()
    {
        return $this->belongsToMany(Route::class)->withPivot('points')->withTimestamps();
    }

    public function staffMembers()
    {
        return $this->belongsToMany(User::class, 'contest_user');
    }

    public function isActive()
    {
        $now = now();
        return $this->start_date <= $now && $this->end_date >= $now;
    }

    public function isPast()
    {
        return $this->end_date < now();
    }

    public function isFuture()
    {
        return $this->start_date > now();
    }

    public function verifiedLogs()
    {
        return Log::whereIn('route_id', $this->routes->pluck('id'))
            ->whereNotNull('verified_by')
            ->whereBetween('created_at', [$this->start_date, $this->end_date])
            ->get();
    }

    public function steps()
    {
        return $this->hasMany(ContestStep::class)->orderBy('order');
    }

    public function getRoutePoints($routeId)
    {
        $route = $this->routes()->where('route_id', $routeId)->first();
        if (!$route) {
            return 0.0;
        }

        $basePoints = (float)$route->pivot->points;
        
        // Calculate dynamic points based on number of climbers who completed it
        $climbersCount = Log::where('route_id', $routeId)
            ->whereNotNull('verified_by')
            ->whereBetween('created_at', [$this->start_date, $this->end_date])
            ->distinct('user_id')
            ->count('user_id');
        
        if ($climbersCount > 0) {
            return round($basePoints / $climbersCount, 2);
        }
        
        return $basePoints;
    }
}
