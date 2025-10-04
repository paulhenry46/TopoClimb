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
        'use_dynamic_points',
        'team_mode',
        'team_points_mode',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'use_dynamic_points' => 'boolean',
        'team_mode' => 'boolean',
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

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function categories()
    {
        return $this->hasMany(ContestCategory::class);
    }

    public function getRoutePoints($routeId)
    {
        $route = $this->routes()->where('route_id', $routeId)->first();
        if (! $route) {
            return 0.0;
        }

        $basePoints = (float) $route->pivot->points;

        // Only apply dynamic calculation if enabled for this contest
        if (! $this->use_dynamic_points) {
            return $basePoints;
        }

        // Calculate dynamic points based on number of climbers who completed it
        $query = Log::where('route_id', $routeId)
            ->whereBetween('created_at', [$this->start_date, $this->end_date]);

        // In official mode, only count verified logs
        // In free mode, count all logs
        if ($this->mode === 'official') {
            $query->whereNotNull('verified_by');
        }

        $climbersCount = $query->distinct('user_id')->count('user_id');

        if ($climbersCount > 0) {
            return round($basePoints / $climbersCount, 2);
        }

        return $basePoints;
    }

    public function getRankingForStep($stepId = null)
    {
        // Get routes - either from step or from contest
        if ($stepId) {
            $step = $this->steps()->find($stepId);
            if (!$step) {
                return collect();
            }
            
            // If step has specific routes assigned, use those. Otherwise use all contest routes
            $routeIds = $step->routes->count() > 0 
                ? $step->routes->pluck('id') 
                : $this->routes->pluck('id');
            
            $startDate = $step->start_time;
            $endDate = $step->end_time;
        } else {
            $routeIds = $this->routes->pluck('id');
            $startDate = $this->start_date;
            $endDate = $this->end_date;
        }

        // Build base query
        $logsQuery = Log::whereIn('route_id', $routeIds)
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Filter by mode
        if ($this->mode === 'official') {
            $logsQuery->whereNotNull('verified_by');
        }

        $logs = $logsQuery->get();

        // Group by user and calculate points
        $rankings = $logs->groupBy('user_id')->map(function ($userLogs, $userId) {
            $uniqueRoutes = $userLogs->pluck('route_id')->unique();
            $totalPoints = $uniqueRoutes->sum(function ($routeId) {
                return $this->getRoutePoints($routeId);
            });

            return [
                'user_id' => $userId,
                'user' => $userLogs->first()->user,
                'routes_count' => $uniqueRoutes->count(),
                'total_points' => $totalPoints,
            ];
        })->sortByDesc('total_points')->values();

        // Add ranking position
        $rankings = $rankings->map(function ($item, $index) {
            $item['rank'] = $index + 1;
            return $item;
        });

        return $rankings;
    }

    public function getUserRankingForStep($userId, $stepId = null)
    {
        $rankings = $this->getRankingForStep($stepId);
        return $rankings->firstWhere('user_id', $userId);
    }

    public function getTeamRankingForStep($stepId = null)
    {
        if (!$this->team_mode) {
            return collect();
        }

        $teams = $this->teams()->with('users')->get();
        
        $rankings = $teams->map(function ($team) {
            $totalPoints = $team->getTotalPoints();
            $routeIds = $this->routes->pluck('id');
            
            // Get unique routes climbed by team
            $logs = Log::whereIn('route_id', $routeIds)
                ->whereIn('user_id', $team->users->pluck('id'))
                ->whereBetween('created_at', [$this->start_date, $this->end_date]);

            if ($this->mode === 'official') {
                $logs->whereNotNull('verified_by');
            }

            $uniqueRoutes = $logs->get()->pluck('route_id')->unique()->count();

            return [
                'team_id' => $team->id,
                'team' => $team,
                'routes_count' => $uniqueRoutes,
                'total_points' => $totalPoints,
            ];
        })->sortByDesc('total_points')->values();

        // Add ranking position
        $rankings = $rankings->map(function ($item, $index) {
            $item['rank'] = $index + 1;
            return $item;
        });

        return $rankings;
    }

    public function getCategoryRankings($categoryId, $stepId = null)
    {
        $category = $this->categories()->find($categoryId);
        if (!$category) {
            return collect();
        }

        $userIds = $category->users->pluck('id');
        $rankings = $this->getRankingForStep($stepId);
        
        // Filter rankings to only include users in this category
        $categoryRankings = $rankings->filter(function ($ranking) use ($userIds) {
            return $userIds->contains($ranking['user_id']);
        })->values();

        // Re-rank within category
        $categoryRankings = $categoryRankings->map(function ($item, $index) {
            $item['rank'] = $index + 1;
            return $item;
        });

        return $categoryRankings;
    }
}
