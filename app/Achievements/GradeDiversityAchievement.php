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

    public function getName(): string
    {
        return 'Explorateur de cotations';
    }

    public function getDescription(): string
    {
        return 'Grimper au moins une voie dans ' . $this->requiredCount . ' cotations différentes';
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
