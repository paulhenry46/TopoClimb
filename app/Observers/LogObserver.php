<?php

namespace App\Observers;

use App\Models\Log;
use App\Models\Contest;
use App\Services\AchievementService;

class LogObserver
{
    /**
     * Handle the Log "created" event.
     */
    public function created(Log $log): void
    {
        // Find active contests that include this route via steps (steps have a many-to-many relation to routes)
        $contests = Contest::whereHas('steps', function ($query) use ($log) {
            $query->whereHas('routes', function ($q) use ($log) {
            $q->where('routes.id', $log->route_id);
            });
        })
        ->where('start_date', '<=', $log->created_at)
        ->where('end_date', '>=', $log->created_at)
        ->get();

        // Auto-assign user to eligible categories in these contests
        foreach ($contests as $contest) {
            $contest->autoAssignUserToCategories($log->user);
        }

        // Evaluate achievements for the user (only for public logs)
        if ($log->is_public) {
            $achievementService = new AchievementService();
            $achievementService->evaluateAchievements($log->user);
        }
    }

    /**
     * Handle the Log "creating" event to set is_public based on type.
     */
    public function creating(Log $log): void
    {
        // Ensure is_public is set correctly based on log type
        if ($log->type === 'tentative' && !isset($log->is_public)) {
            $log->is_public = false;
        } elseif (!isset($log->is_public)) {
            $log->is_public = true;
        }
    }

    /**
     * Handle the Log "updated" event.
     */
    public function updated(Log $log): void
    {
        //
    }

    /**
     * Handle the Log "deleted" event.
     */
    public function deleted(Log $log): void
    {
        //
    }

    /**
     * Handle the Log "restored" event.
     */
    public function restored(Log $log): void
    {
        //
    }

    /**
     * Handle the Log "force deleted" event.
     */
    public function forceDeleted(Log $log): void
    {
        //
    }
}
