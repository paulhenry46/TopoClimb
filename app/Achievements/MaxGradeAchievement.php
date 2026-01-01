<?php

namespace App\Achievements;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class MaxGradeAchievement extends BaseAchievement
{
    private int $requiredGrade;
    private string $gradeLabel;

    public function __construct(int $requiredGrade, string $gradeLabel)
    {
        $this->requiredGrade = $requiredGrade;
        $this->gradeLabel = $gradeLabel;
    }

    public function getKey(): string
    {
        return 'max_grade_' . $this->requiredGrade;
    }

    public function getName(): string
    {
        return 'Grimpeur ' . $this->gradeLabel;
    }

    public function getDescription(): string
    {
        return 'Réaliser une voie de cotation ' . $this->gradeLabel . ' ou plus';
    }

    public function getType(): string
    {
        return 'max_grade';
    }

    public function getCriteria(): array
    {
        return [
            'required_grade' => $this->requiredGrade,
        ];
    }

    public function isUnlocked(User $user): bool
    {
        // Check if user has any log with grade >= required grade
        return DB::table('logs')
            ->where('user_id', $user->id)
            ->where('grade', '>=', $this->requiredGrade)
            ->exists();
    }
}
