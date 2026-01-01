<?php

namespace App\Achievements;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class DailyMarathonAchievement extends BaseAchievement
{
    private int $requiredCount;

    public function __construct(int $requiredCount)
    {
        $this->requiredCount = $requiredCount;
    }

    public function getKey(): string
    {
        return 'daily_marathon_' . $this->requiredCount;
    }

    public function getName(): string
    {
        return 'Marathon de grimpe';
    }

    public function getDescription(): string
    {
        return 'Grimper ' . $this->requiredCount . ' voies dans la même journée';
    }

    public function getType(): string
    {
        return 'daily_marathon';
    }

    public function getCriteria(): array
    {
        return [
            'required_count' => $this->requiredCount,
        ];
    }

    public function isUnlocked(User $user): bool
    {
        // Check if user has logged at least requiredCount routes on any single day
        $maxRoutesInDay = DB::table('logs')
            ->where('user_id', $user->id)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(DISTINCT route_id) as count'))
            ->groupBy('date')
            ->orderBy('count', 'desc')
            ->first();

        return $maxRoutesInDay && $maxRoutesInDay->count >= $this->requiredCount;
    }
}
