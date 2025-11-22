<?php

namespace App\Observers;

use App\Models\Contest;
use App\Models\Log;

class LogObserver
{
    /**
     * Handle the Log "created" event.
     */
    public function created(Log $log): void
    {
        // Find active contests that include this route
        // We need to join through contest_step_route to find contests
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
