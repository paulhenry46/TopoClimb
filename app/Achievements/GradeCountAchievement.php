<?php

namespace App\Achievements;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class GradeCountAchievement extends BaseAchievement
{
    private int $minGrade;
    private int $requiredCount;
    private string $gradeLabel;

    public function __construct(int $minGrade, int $requiredCount, string $gradeLabel)
    {
        $this->minGrade = $minGrade;
        $this->requiredCount = $requiredCount;
        $this->gradeLabel = $gradeLabel;
    }

    public function getKey(): string
    {
        return 'grade_count_' . $this->minGrade . '_' . $this->requiredCount;
    }

    public function getName(): string
    {
        return __(':count routes in grade :grade+', ['count' => $this->requiredCount, 'grade' => $this->gradeLabel]);
    }

    public function getDescription(): string
    {
        return __('Climb :count routes graded :grade or higher', ['count' => $this->requiredCount, 'grade' => $this->gradeLabel]);
    }

    public function getType(): string
    {
        return 'grade_count';
    }

    public function getCriteria(): array
    {
        return [
            'min_grade' => $this->minGrade,
            'required_count' => $this->requiredCount,
        ];
    }

    public function isUnlocked(User $user): bool
    {
        // Count distinct routes at or above the minimum grade
        $count = DB::table('logs')
            ->where('user_id', $user->id)
            ->where('grade', '>=', $this->minGrade)
            ->distinct('route_id')
            ->count('route_id');

        return $count >= $this->requiredCount;
    }
}
