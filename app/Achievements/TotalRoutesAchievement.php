<?php

namespace App\Achievements;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class TotalRoutesAchievement extends BaseAchievement
{
    private int $requiredCount;

    public function __construct(int $requiredCount)
    {
        $this->requiredCount = $requiredCount;
    }

    public function getKey(): string
    {
        return 'total_routes_' . $this->requiredCount;
    }

    public function getName(): string
    {
        return $this->requiredCount . ' voies grimpées';
    }

    public function getDescription(): string
    {
        return 'Grimper un total de ' . $this->requiredCount . ' voies';
    }

    public function getType(): string
    {
        return 'total_routes';
    }

    public function getCriteria(): array
    {
        return [
            'required_count' => $this->requiredCount,
        ];
    }

    public function isUnlocked(User $user): bool
    {
        // Count distinct routes climbed by the user
        $count = DB::table('logs')
            ->where('user_id', $user->id)
            ->distinct('route_id')
            ->count('route_id');

        return $count >= $this->requiredCount;
    }
}
