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
        'max_users',
        'created_by',
        'invitation_token',
    ];

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isFull()
    {
        return $this->users()->count() >= $this->max_users;
    }

    public function canAddUser($user = null)
    {
        // Admin can always add users
        // In 'free' mode, team members can add users if not full
        // In 'register' mode, users can join if not full
        // In 'restricted' mode, only admins can add users
        
        $contest = $this->contest;
        
        if (!$contest->team_mode) {
            return false;
        }

        // If team is full and user is not admin, cannot add
        if ($this->isFull() && $user && !$user->hasRole('super-admin') && !$contest->isStaffMember($user)) {
            // Only admins can exceed the limit
            return false;
        }

        return true;
    }

    public function generateInvitationToken()
    {
        $this->invitation_token = bin2hex(random_bytes(32));
        $this->save();
        return $this->invitation_token;
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
