<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contest extends Model
{
    use HasFactory;
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

    protected static function booted()
    {
        // When a contest is created, create the main step
        static::created(function ($contest) {
            // Create main step with contest dates
            ContestStep::create([
                'contest_id' => $contest->id,
                'name' => 'Main',
                'order' => 0,
                'start_time' => $contest->start_date,
                'end_time' => $contest->end_date,
            ]);
            
            // If in official mode, create the permission
            if ($contest->mode === 'official') {
                $contest->createStaffPermission();
            }
        });

        // When a contest is updated, manage permission based on mode
        static::updated(function ($contest) {
            if ($contest->mode === 'official') {
                // Ensure permission exists
                $contest->createStaffPermission();
            } else {
                // Delete permission if mode changed from official to free
                $contest->deleteStaffPermission();
            }
            
            // Update main step dates if contest dates changed
            if ($contest->isDirty(['start_date', 'end_date'])) {
                $mainStep = $contest->steps()->where('order', 0)->where('name', 'Main')->first();
                if ($mainStep) {
                    $mainStep->update([
                        'start_time' => $contest->start_date,
                        'end_time' => $contest->end_date,
                    ]);
                }
            }
        });

        // When a contest is deleted, delete the permission
        static::deleted(function ($contest) {
            $contest->deleteStaffPermission();
        });
    }

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function routes()
    {
        // This method returns the belongsToMany relationship through the main step
        // We need to use a subquery to filter by the main step
        return $this->belongsToMany(
            Route::class,
            'contest_step_route',
            'contest_step_id',
            'route_id'
        )
        ->wherePivotIn('contest_step_id', function($query) {
            $query->select('id')
                  ->from('contest_steps')
                  ->where('contest_id', $this->id);
        })
        ->withPivot('points')
        ->withTimestamps();
    }

    public function staffMembers()
    {
        // Get users with the contest.{id} permission
        if ($this->mode !== 'official') {
            return User::whereRaw('1=0'); // Return empty query for non-official contests
        }
        
        $permissionName = 'contest.' . $this->id;
        return User::permission($permissionName);
    }
    
    public function addStaffMember(User $user)
    {
        if ($this->mode !== 'official') {
            return false; // Only official contests can have staff
        }
        
        $permissionName = 'contest.' . $this->id;
        
        // Create permission if it doesn't exist
        $permission = \Spatie\Permission\Models\Permission::firstOrCreate([
            'name' => $permissionName,
            'guard_name' => 'web'
        ]);
        
        // Give permission to user
        $user->givePermissionTo($permission);
        
        return true;
    }
    
    public function removeStaffMember(User $user)
    {
        $permissionName = 'contest.' . $this->id;
        
        if ($user->hasPermissionTo($permissionName)) {
            $user->revokePermissionTo($permissionName);
        }
        
        return true;
    }
    
    public function isStaffMember(User $user)
    {
        if ($this->mode !== 'official') {
            return false;
        }
        
        $permissionName = 'contest.' . $this->id;
        return $user->hasPermissionTo($permissionName);
    }
    
    public function createStaffPermission()
    {
        if ($this->mode === 'official') {
            $permissionName = 'contest.' . $this->id;
            \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web'
            ]);
        }
    }
    
    public function deleteStaffPermission()
    {
        $permissionName = 'contest.' . $this->id;
        $permission = \Spatie\Permission\Models\Permission::where('name', $permissionName)->first();
        
        if ($permission) {
            $permission->delete();
        }
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

    public function mainStep()
    {
        return $this->steps()->where('order', 0)->where('name', 'Main')->first();
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function categories()
    {
        return $this->hasMany(ContestCategory::class);
    }

    public function getRoutePoints($routeId, $step)
    {
        // If a specific step was provided, prefer that step's pivot points
        if ($step instanceof ContestStep) {
            $route = $step->routes()->where('routes.id', $routeId)->first();
            if (! $route) {
            return 0.0;
            }

            $basePoints = (float) $route->pivot->points;
        } else {
            // No specific step passed: find all steps of this contest that include the route
            $stepsWithRoute = $this->steps()
            ->whereHas('routes', function ($q) use ($routeId) {
                $q->where('routes.id', $routeId);
            })
            ->with(['routes' => function ($q) use ($routeId) {
                $q->where('routes.id', $routeId);
            }])
            ->get();

            $basePoints = 0.0;
            foreach ($stepsWithRoute as $s) {
            $r = $s->routes->first();
            if ($r && isset($r->pivot->points)) {
                $points = (float) $r->pivot->points;
                if ($points > $basePoints) {
                $basePoints = $points;
                }
            }
            }
        }

        // Only apply dynamic calculation if enabled for this contest
        if (! $this->use_dynamic_points) {
            return $basePoints;
        }

        // Calculate dynamic points based on number of climbers who completed it
        $query = Log::where('route_id', $routeId)
            ->whereBetween('created_at', [$this->start_date, $this->end_date]);

        // In official mode, only count verified logs
        if ($this->mode === 'official') {
            $query->whereNotNull('verified_by');
        }

        $climbersCount = $query->distinct('user_id')->count('user_id');

        if ($climbersCount > 0) {
            return round($basePoints / $climbersCount, 2);
        }

        return $basePoints;
    }

    public function getRankingForStep( $stepId, $restricted = false)
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
            // Use routes assigned to each step (rather than contest-level routes)
            $routeIds = $this->steps()->with('routes')->get()
                ->flatMap(function ($s) {
                    return $s->routes->pluck('id');
                })->unique()->values();

            $startDate = $this->start_date;
            $endDate = $this->end_date;
            $step = null;
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
        $rankings = $logs->groupBy('user_id')->map(function ($userLogs, $userId) use ($step, $restricted) {
            $uniqueRoutes = $userLogs->pluck('route_id')->unique();
            $totalPoints = $uniqueRoutes->sum(function ($routeId) use ($step) {
                return $this->getRoutePoints($routeId, $step);
            });
            if($restricted){
                return [
                'user_id' => $userId,
                'user_name' => $userLogs->first()->user->name,
                'routes_count' => $uniqueRoutes->count(),
                'total_points' => $totalPoints,
            ];
            }else{
                return [
                'user_id' => $userId,
                'user' => $userLogs->first()->user,
                'routes_count' => $uniqueRoutes->count(),
                'total_points' => $totalPoints,
            ];
            }
            
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

    public function getCategoryRankings($categoryId, $stepId = null, $restricted = false)
    {
        $category = $this->categories()->find($categoryId);
        if (!$category) {
            return collect();
        }

        $userIds = $category->users->pluck('id');
        $rankings = $this->getRankingForStep($stepId, $restricted);

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

    public function autoAssignUserToCategories(User $user)
    {
        $autoAssignCategories = $this->categories()->where('auto_assign', true)->get();
        
        foreach ($autoAssignCategories as $category) {
            if ($category->userMatches($user)) {
                // Sync without detaching to avoid removing existing category memberships
                $category->users()->syncWithoutDetaching([$user->id]);
            }
        }
    }
}
