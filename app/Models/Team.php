<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'contest_id',
    ];

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function getTotalPoints()
    {
        $contest = $this->contest;
        $routeIds = $contest->routes->pluck('id');
        
        // Get logs from all team members
        $logs = Log::whereIn('route_id', $routeIds)
            ->whereIn('user_id', $this->users->pluck('id'))
            ->whereBetween('created_at', [$contest->start_date, $contest->end_date]);

        if ($contest->mode === 'official') {
            $logs->whereNotNull('verified_by');
        }

        $logs = $logs->get();

        // Check team points mode
        if ($contest->team_points_mode === 'all') {
            // Sum all climbs (including duplicates)
            return $logs->sum(function ($log) use ($contest) {
                return $contest->getRoutePoints($log->route_id);
            });
        } else {
            // Default: unique routes only
            $uniqueRoutes = $logs->pluck('route_id')->unique();
            
            return $uniqueRoutes->sum(function ($routeId) use ($contest) {
                return $contest->getRoutePoints($routeId);
            });
        }
    }
}
