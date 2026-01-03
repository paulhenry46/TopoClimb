<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserStats;
use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatsCalculationService
{
    /**
     * Calculate and update all statistics for a user.
     */
    public function calculateStatsForUser(User $user): void
    {
        $stats = $user->stats ?? new UserStats(['user_id' => $user->id]);

        // Get all user logs (including tentative for calculations)
        $allLogs = $user->logs()->with(['route.tags', 'route.line.sector'])->get();
        
        // Get only public logs (excluding tentative)
        $publicLogs = $allLogs->where('is_public', true);

        if ($allLogs->isEmpty()) {
            return;
        }

        // Calculate technical analysis metrics
        $this->calculateTechnicalAnalysis($stats, $allLogs, $publicLogs);

        // Calculate behavioral analysis metrics
        $this->calculateBehavioralAnalysis($stats, $allLogs);

        // Calculate progression analysis metrics
        $this->calculateProgressionAnalysis($stats, $allLogs, $publicLogs);

        // Calculate training load analysis metrics
        $this->calculateTrainingLoadAnalysis($stats, $allLogs);

        $stats->last_calculated_at = now();
        $stats->save();
    }

    /**
     * Calculate technical analysis metrics.
     */
    protected function calculateTechnicalAnalysis($stats, $allLogs, $publicLogs): void
    {
        // Consistency variance - measure variance in difficulty levels
        $grades = $publicLogs->pluck('grade')->filter();
        if ($grades->count() > 1) {
            $mean = $grades->avg();
            $variance = $grades->map(fn($grade) => pow($grade - $mean, 2))->avg();
            $stats->consistency_variance = round($variance, 2);
        }

        // Flash/Work ratio
        $flashCount = $publicLogs->where('type', 'flash')->count();
        $workCount = $publicLogs->where('type', 'work')->count();
        if ($workCount > 0) {
            $stats->flash_work_ratio = round($flashCount / $workCount, 2);
        } elseif ($flashCount > 0) {
            $stats->flash_work_ratio = 999.99; // Very high ratio
        }

        // Risk profile - abandonment rate (tentative logs that never led to success)
        $tentativeLogs = $allLogs->where('type', 'tentative');
        $uniqueRoutesAttempted = $tentativeLogs->pluck('route_id')->unique();
        $routesCompleted = $publicLogs->whereIn('route_id', $uniqueRoutesAttempted)->pluck('route_id')->unique();
        $routesAbandoned = $uniqueRoutesAttempted->diff($routesCompleted);
        
        if ($uniqueRoutesAttempted->count() > 0) {
            $stats->risk_profile_abandonment_rate = round(
                ($routesAbandoned->count() / $uniqueRoutesAttempted->count()) * 100,
                2
            );
        }

        // Average difficulty of abandoned routes
        if ($routesAbandoned->count() > 0) {
            $abandonedGrades = $tentativeLogs->whereIn('route_id', $routesAbandoned)->pluck('grade')->filter();
            if ($abandonedGrades->count() > 0) {
                $stats->avg_difficulty_abandoned = round($abandonedGrades->avg(), 2);
            }
        }

        // Long routes vs short routes (based on route type or tags)
        // Assuming routes with 'continuity' or 'endurance' tags are long
        $longRoutesLogs = $publicLogs->filter(function ($log) {
            return $log->route && $log->route->tags()->whereIn('name', ['continuity', 'endurance', 'resistance'])->exists();
        });
        $stats->long_routes_count = $longRoutesLogs->count();
        $stats->short_routes_count = $publicLogs->count() - $longRoutesLogs->count();

        // Average time between attempts on same route
        $attemptTimes = [];
        foreach ($allLogs->groupBy('route_id') as $routeId => $routeLogs) {
            if ($routeLogs->count() > 1) {
                $sorted = $routeLogs->sortBy('created_at');
                for ($i = 1; $i < $sorted->count(); $i++) {
                    $timeDiff = $sorted->values()[$i]->created_at->diffInHours($sorted->values()[$i - 1]->created_at);
                    $attemptTimes[] = $timeDiff;
                }
            }
        }
        if (count($attemptTimes) > 0) {
            $stats->avg_time_between_attempts = round(array_sum($attemptTimes) / count($attemptTimes), 2);
        }

        // Movement type preferences based on route tags
        $tagCounts = [];
        foreach ($publicLogs as $log) {
            if ($log->route && $log->route->tags) {
                foreach ($log->route->tags as $tag) {
                    $tagCounts[$tag->name] = ($tagCounts[$tag->name] ?? 0) + 1;
                }
            }
        }
        if (!empty($tagCounts)) {
            arsort($tagCounts);
            $stats->movement_preferences = array_slice($tagCounts, 0, 10, true);
        }
    }

    /**
     * Calculate behavioral analysis metrics.
     */
    protected function calculateBehavioralAnalysis($stats, $allLogs): void
    {
        // Preferred climbing hour
        $hourCounts = [];
        foreach ($allLogs as $log) {
            $hour = $log->created_at->format('H');
            $hourCounts[$hour] = ($hourCounts[$hour] ?? 0) + 1;
        }
        if (!empty($hourCounts)) {
            arsort($hourCounts);
            $preferredHour = array_key_first($hourCounts);
            $stats->preferred_climbing_hour = $preferredHour . ':00';
        }

        // Average session duration and routes per session
        // Group logs by date to identify sessions (same day = same session for simplicity)
        $sessions = $allLogs->groupBy(fn($log) => $log->created_at->format('Y-m-d'));
        
        $sessionDurations = [];
        $routesPerSession = [];
        
        foreach ($sessions as $date => $sessionLogs) {
            if ($sessionLogs->count() > 1) {
                $first = $sessionLogs->min('created_at');
                $last = $sessionLogs->max('created_at');
                $duration = $last->diffInHours($first);
                $sessionDurations[] = $duration;
            }
            $routesPerSession[] = $sessionLogs->count();
        }

        if (count($sessionDurations) > 0) {
            $stats->avg_session_duration = round(array_sum($sessionDurations) / count($sessionDurations), 2);
        }
        if (count($routesPerSession) > 0) {
            $stats->avg_routes_per_session = round(array_sum($routesPerSession) / count($routesPerSession), 2);
        }

        // Exploration ratio - new routes vs repeated routes
        $uniqueRoutes = $allLogs->pluck('route_id')->unique()->count();
        $totalLogs = $allLogs->count();
        if ($totalLogs > 0) {
            $stats->exploration_ratio = round(($uniqueRoutes / $totalLogs) * 100, 2);
        }

        // Sector fidelity - most climbed sectors
        $sectorCounts = [];
        foreach ($allLogs as $log) {
            if ($log->route && $log->route->line && $log->route->line->sector) {
                $sectorName = $log->route->line->sector->name;
                $sectorCounts[$sectorName] = ($sectorCounts[$sectorName] ?? 0) + 1;
            }
        }
        if (!empty($sectorCounts)) {
            arsort($sectorCounts);
            $stats->sector_fidelity = array_slice($sectorCounts, 0, 5, true);
        }

        // Average attempts before success
        $attemptsBeforeSuccess = [];
        foreach ($allLogs->groupBy('route_id') as $routeId => $routeLogs) {
            $successLog = $routeLogs->whereIn('type', ['work', 'flash', 'view'])->first();
            if ($successLog) {
                $attempts = $routeLogs->where('created_at', '<', $successLog->created_at)->count();
                if ($attempts > 0) {
                    $attemptsBeforeSuccess[] = $attempts;
                }
            }
        }
        if (count($attemptsBeforeSuccess) > 0) {
            $stats->avg_attempts_before_success = round(array_sum($attemptsBeforeSuccess) / count($attemptsBeforeSuccess), 2);
        }

        // Project count - routes worked over multiple sessions
        $projectCount = 0;
        foreach ($allLogs->groupBy('route_id') as $routeId => $routeLogs) {
            $dates = $routeLogs->pluck('created_at')->map(fn($date) => $date->format('Y-m-d'))->unique();
            if ($dates->count() > 1) {
                $projectCount++;
            }
        }
        $stats->project_count = $projectCount;
    }

    /**
     * Calculate progression analysis metrics.
     */
    protected function calculateProgressionAnalysis($stats, $allLogs, $publicLogs): void
    {
        // Progression rate - level progression per month
        if ($publicLogs->count() > 1) {
            $sortedLogs = $publicLogs->sortBy('created_at');
            $firstLog = $sortedLogs->first();
            $lastLog = $sortedLogs->last();
            
            $monthsDiff = $firstLog->created_at->diffInMonths($lastLog->created_at);
            if ($monthsDiff > 0) {
                $gradeDiff = $lastLog->grade - $firstLog->grade;
                $stats->progression_rate = round($gradeDiff / $monthsDiff, 2);
            }
        }

        // Plateau detection - check if no progression in last 8 weeks
        $recentLogs = $publicLogs->where('created_at', '>', now()->subWeeks(8))->sortBy('created_at');
        if ($recentLogs->count() > 5) {
            $firstGrade = $recentLogs->first()->grade;
            $lastGrade = $recentLogs->last()->grade;
            $gradeDiff = abs($lastGrade - $firstGrade);
            
            if ($gradeDiff < 10) { // Less than 10 points progression
                $stats->plateau_detected = true;
                $stats->plateau_weeks = 8;
            } else {
                $stats->plateau_detected = false;
                $stats->plateau_weeks = 0;
            }
        }

        // Progression by style (using tags)
        $progressionByStyle = [];
        $styleGroups = ['dalle' => ['slab', 'dalle'], 'devers' => ['overhang', 'devers'], 'vertical' => ['vertical']];
        
        foreach ($styleGroups as $styleName => $tags) {
            $styleLogs = $publicLogs->filter(function ($log) use ($tags) {
                if (!$log->route || !$log->route->tags) return false;
                $routeTags = $log->route->tags->pluck('name')->map(fn($t) => strtolower($t))->toArray();
                return !empty(array_intersect($routeTags, $tags));
            })->sortBy('created_at');

            if ($styleLogs->count() > 1) {
                $firstLog = $styleLogs->first();
                $lastLog = $styleLogs->last();
                $monthsDiff = $firstLog->created_at->diffInMonths($lastLog->created_at);
                if ($monthsDiff > 0) {
                    $gradeDiff = $lastLog->grade - $firstLog->grade;
                    $progressionByStyle[$styleName] = round($gradeDiff / $monthsDiff, 2);
                }
            }
        }
        if (!empty($progressionByStyle)) {
            $stats->progression_by_style = $progressionByStyle;
        }

        // Progression by sector
        $progressionBySector = [];
        foreach ($publicLogs->groupBy(fn($log) => $log->route->line->sector->name ?? 'Unknown') as $sector => $sectorLogs) {
            if ($sectorLogs->count() > 1) {
                $sortedSectorLogs = $sectorLogs->sortBy('created_at');
                $firstLog = $sortedSectorLogs->first();
                $lastLog = $sortedSectorLogs->last();
                $monthsDiff = $firstLog->created_at->diffInMonths($lastLog->created_at);
                if ($monthsDiff > 0) {
                    $gradeDiff = $lastLog->grade - $firstLog->grade;
                    $progressionBySector[$sector] = round($gradeDiff / $monthsDiff, 2);
                }
            }
        }
        if (!empty($progressionBySector)) {
            arsort($progressionBySector);
            $stats->progression_by_sector = array_slice($progressionBySector, 0, 5, true);
        }
    }

    /**
     * Calculate training load analysis metrics.
     */
    protected function calculateTrainingLoadAnalysis($stats, $allLogs): void
    {
        // Weekly volume - number of routes × difficulty
        $weeklyLogs = $allLogs->where('created_at', '>', now()->subWeek());
        $weeklyVolume = 0;
        foreach ($weeklyLogs as $log) {
            $weeklyVolume += $log->grade;
        }
        $stats->weekly_volume = round($weeklyVolume, 2);

        // Weekly intensity - average difficulty
        if ($weeklyLogs->count() > 0) {
            $stats->weekly_intensity = round($weeklyLogs->avg('grade'), 2);
        }

        // Acute load (7 days)
        $acuteLogs = $allLogs->where('created_at', '>', now()->subDays(7));
        $acuteLoad = 0;
        foreach ($acuteLogs as $log) {
            $acuteLoad += $log->grade;
        }
        $stats->acute_load = round($acuteLoad, 2);

        // Chronic load (28 days)
        $chronicLogs = $allLogs->where('created_at', '>', now()->subDays(28));
        $chronicLoad = 0;
        foreach ($chronicLogs as $log) {
            $chronicLoad += $log->grade;
        }
        $stats->chronic_load = round($chronicLoad / 4, 2); // Average per week

        // Acute/Chronic ratio
        if ($stats->chronic_load > 0) {
            $stats->acute_chronic_ratio = round($stats->acute_load / $stats->chronic_load, 2);
            
            // Overtraining detection - ratio > 1.5 indicates potential overtraining
            $stats->overtraining_detected = $stats->acute_chronic_ratio > 1.5;
        }

        // Average recovery time - time between sessions
        $sessionDates = $allLogs->sortBy('created_at')
            ->pluck('created_at')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->unique()
            ->values();

        $recoveryTimes = [];
        for ($i = 1; $i < $sessionDates->count(); $i++) {
            $prev = Carbon::parse($sessionDates[$i - 1]);
            $curr = Carbon::parse($sessionDates[$i]);
            $recoveryTimes[] = $prev->diffInHours($curr);
        }

        if (count($recoveryTimes) > 0) {
            $stats->avg_recovery_time = round(array_sum($recoveryTimes) / count($recoveryTimes), 2);
        }

        // Average time between big performances (top 10% grades)
        $topGrade = $allLogs->max('grade');
        $threshold = $topGrade * 0.9; // Top 10%
        $performanceLogs = $allLogs->where('grade', '>=', $threshold)->sortBy('created_at');
        
        $performanceTimes = [];
        if ($performanceLogs->count() > 1) {
            $performanceArray = $performanceLogs->values();
            for ($i = 1; $i < $performanceLogs->count(); $i++) {
                $timeDiff = $performanceArray[$i]->created_at->diffInHours($performanceArray[$i - 1]->created_at);
                $performanceTimes[] = $timeDiff;
            }
        }

        if (count($performanceTimes) > 0) {
            $stats->avg_time_between_performances = round(array_sum($performanceTimes) / count($performanceTimes), 2);
        }
    }

    /**
     * Calculate stats for all users.
     */
    public function calculateStatsForAllUsers(): void
    {
        User::with('logs')->chunk(100, function ($users) {
            foreach ($users as $user) {
                try {
                    $this->calculateStatsForUser($user);
                } catch (\Exception $e) {
                    \Log::error("Error calculating stats for user {$user->id}: " . $e->getMessage());
                }
            }
        });
    }
}
