<?php

namespace App\Achievements;

use App\Models\User;
use App\Models\Contest;
use Illuminate\Support\Facades\DB;

class ContestAchievement extends BaseAchievement
{
    private int $contestId;
    private string $name;
    private string $description;

    /**
     * @param int $contestId
     * @param string $name (should be translated with __() before passing)
     * @param string $description (should be translated with __() before passing)
     */
    public function __construct(int $contestId, string $name, string $description)
    {
        $this->contestId = $contestId;
        $this->name = $name;
        $this->description = $description;
    }

    public function getKey(): string
    {
        return 'contest_' . $this->contestId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getType(): string
    {
        return 'contest';
    }

    public function getCriteria(): array
    {
        return [
            'contest_id' => $this->contestId,
        ];
    }

    public function getContestId(): ?int
    {
        return $this->contestId;
    }

    public function isUnlocked(User $user): bool
    {
        // Check if user has participated in this contest
        // (has at least one log for a route in this contest)
        $contest = Contest::find($this->contestId);
        
        if (!$contest) {
            return false;
        }

        // Get all route IDs from this contest's steps
        $routeIds = DB::table('contest_step_route')
            ->join('contest_steps', 'contest_step_route.contest_step_id', '=', 'contest_steps.id')
            ->where('contest_steps.contest_id', $this->contestId)
            ->pluck('contest_step_route.route_id');

        if ($routeIds->isEmpty()) {
            return false;
        }

        // Check if user has logs for any of these routes
        return DB::table('logs')
            ->where('user_id', $user->id)
            ->whereIn('route_id', $routeIds)
            ->exists();
    }
}
