<?php

namespace App\Achievements;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UniqueRoutesAchievement extends BaseAchievement
{
    private int $requiredCount;

    public function __construct(int $requiredCount)
    {
        $this->requiredCount = $requiredCount;
    }

    public function getKey(): string
    {
        return 'unique_routes_' . $this->requiredCount;
    }

    public function getName(): string
    {
        return __('Collector');
    }

    public function getDescription(): string
    {
        return __('Climb :count different routes', ['count' => $this->requiredCount]);
    }

    public function getType(): string
    {
        return 'unique_routes';
    }

    public function getCriteria(): array
    {
        return [
            'required_count' => $this->requiredCount,
        ];
    }

    public function isUnlocked(User $user): bool
    {
        // Count distinct routes climbed
        $uniqueRoutes = DB::table('logs')
            ->where('user_id', $user->id)
            ->distinct('route_id')
            ->count('route_id');

        return $uniqueRoutes >= $this->requiredCount;
    }
}
