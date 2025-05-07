<?php

namespace App\Policies;

use App\Models\Site;
use App\Models\User;
use Illuminate\Auth\Access\Response;
/**
 * This class is used to manage permissions depending of in which site user wants to 
 * perform operation
 * 
 */
class SitePolicy
{
    /**
     * Determine whether the user can edit routes of this site.
     */
    public function edit_route(User $user, Site $site): bool
    {
        return $user->can('routes.'.$site->id.'');
    }

    /**
     * Determine whether the user can edit areas of this site.
     */
    public function edit_areas(User $user, Site $site): bool
    {
        return $user->can('areas.'.$site->id.'');
    }

    /**
     * Determine whether the user can edit lines/sectors of this site.
     */
    public function edit_lines_sectors(User $user, Site $site): bool
    {
        return $user->can('lines-sectors.'.$site->id.'');
    }

     /**
     * Determine whether the user can edit lines/sectors of this site.
     */
    public function edit(User $user, Site $site): bool
    {
        return $user->can('site.'.$site->id.'');
    }

    public function users(User $user){
        return $user->hr() == 'owner';
    }

}
