<?php

namespace App\Achievements;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class GradeDiversityAchievement extends BaseAchievement
{
    private int $requiredCount;

    public function __construct(int $requiredCount)
    {
        $this->requiredCount = $requiredCount;
    }

    public function getKey(): string
    {
        return 'grade_diversity_' . $this->requiredCount;
    }

    /**
     * Name and description are already translated using __().
     */
    public function getName(): string
    {
        return __('Grade explorer');
    }

    public function getDescription(): string
    {
        return __('Climb at least one route in :count different grades', ['count' => $this->requiredCount]);
    }

    public function getType(): string
    {
        return 'grade_diversity';
    }

    public function getCriteria(): array
    {
        return [
            'required_count' => $this->requiredCount,
        ];
    }

    public function isUnlocked(User $user): bool
    {
        // Count distinct grades climbed by the user
        $distinctGrades = DB::table('logs')
            ->where('user_id', $user->id)
            ->distinct('grade')
            ->count('grade');

        return $distinctGrades >= $this->requiredCount;
    }
}
