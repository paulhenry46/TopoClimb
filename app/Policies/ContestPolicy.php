<?php

namespace App\Policies;

use App\Models\Contest;
use App\Models\User;

class ContestPolicy
{
    /**
     * Determine whether the user can access contest registrations.
     * This allows users with contest.{id} permission OR site edit_areas permission.
     */
    public function access_registrations(User $user, Contest $contest): bool
    {
        // Check if user has contest staff permission
        if ($contest->mode === 'official') {
            $permissionName = 'contest.' . $contest->id;
            if ($user->hasPermissionTo($permissionName)) {
                return true;
            }
        }
        
        // Check if user has site edit_areas permission
        return $user->can('areas.' . $contest->site_id);
    }
}
