<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\User;
use App\Models\UserAchievement;
use App\Achievements\BaseAchievement;
use App\Achievements\MaxGradeAchievement;
use App\Achievements\TotalRoutesAchievement;
use App\Achievements\GradeCountAchievement;
use App\Achievements\ContestAchievement;
use App\Achievements\GradeDiversityAchievement;
use App\Achievements\DailyMarathonAchievement;
use App\Achievements\WeeklyRegularityAchievement;
use App\Achievements\UniqueRoutesAchievement;

class AchievementService
{
    /**
     * Get all available achievement definitions.
     * 
     * @return array<BaseAchievement>
     */
    public function getAllAchievementDefinitions(): array
    {
        $achievements = [];

        // Get grade definitions from config
        $cotationPoints = config('climb.default_cotation.points');
        $cotationReverse = config('climb.default_cotation_reverse');

        // Max grade achievements - select grades from config
        $gradeKeys = array_keys($cotationReverse);
        $selectedGrades = [];
        $minSeparation = 100;
        if (count($gradeKeys) <= 10) {
            foreach ($gradeKeys as $key) {
                $selectedGrades[] = $cotationReverse[$key];
            }
        } else {
            $lastKey = null;
            foreach ($gradeKeys as $key) {
                if ($lastKey === null || ($key - $lastKey) >= $minSeparation) {
                    $selectedGrades[] = $cotationReverse[$key];
                    $lastKey = $key;
                }
            }
        }
        foreach ($selectedGrades as $gradeLabel) {
            if (isset($cotationPoints[$gradeLabel])) {
                $grade = $cotationPoints[$gradeLabel];
                $achievements[] = new MaxGradeAchievement($grade, $gradeLabel);
            }
        }

        // Total routes achievements
        $routeCounts = [10, 25, 50, 100, 250, 500];
        foreach ($routeCounts as $count) {
            $achievements[] = new TotalRoutesAchievement($count);
        }

        // Grade count achievements - use config for grades

        $gradeCountDefinitions = [];
        foreach ($selectedGrades as $gradeLabel) {
            $gradeCountDefinitions[] = [
                'label' => $gradeLabel,
                'count' => 10,
            ];
        }

        foreach ($gradeCountDefinitions as $def) {
            if (isset($cotationPoints[$def['label']])) {
                $grade = $cotationPoints[$def['label']];
                $achievements[] = new GradeCountAchievement($grade, $def['count'], $def['label']);
            }
        }

        // Grade diversity achievements
        $achievements[] = new GradeDiversityAchievement(6);

        // Daily marathon achievements
        $achievements[] = new DailyMarathonAchievement(20);

        // Weekly regularity achievements
        $achievements[] = new WeeklyRegularityAchievement(4);  // Régulier
        $achievements[] = new WeeklyRegularityAchievement(12); // Jamais sans ma salle

        // Unique routes (collector) achievements
        $routeCollectorCounts = [50, 100, 200];
        foreach ($routeCollectorCounts as $count) {
            $achievements[] = new UniqueRoutesAchievement($count);
        }

        return $achievements;
    }

    /**
     * Synchronize achievement definitions to the database.
     */
    public function syncAchievements(): void
    {
        $definitions = $this->getAllAchievementDefinitions();

        foreach ($definitions as $definition) {
            Achievement::updateOrCreate(
                ['key' => $definition->getKey()],
                $definition->toArray()
            );
        }
    }

    /**
     * Evaluate and award achievements for a specific user.
     * 
     * @param User $user
     * @return array Array of newly unlocked achievement keys
     */
    public function evaluateAchievements(User $user): array
    {
        $definitions = $this->getAllAchievementDefinitions();
        $newlyUnlocked = [];

        foreach ($definitions as $definition) {
            // Skip if already unlocked
            if ($user->hasAchievement($definition->getKey())) {
                continue;
            }

            // Check if the achievement is now unlocked
            if ($definition->isUnlocked($user)) {
                $achievement = Achievement::where('key', $definition->getKey())->first();
                
                if ($achievement) {
                    // Use firstOrCreate to handle race conditions gracefully
                    $userAchievement = UserAchievement::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'achievement_id' => $achievement->id,
                        ],
                        [
                            'unlocked_at' => now(),
                        ]
                    );

                    // Only add to newly unlocked if it was just created
                    if ($userAchievement->wasRecentlyCreated) {
                        $newlyUnlocked[] = $definition->getKey();
                    }
                }
            }
        }

        return $newlyUnlocked;
    }

    /**
     * Create a contest-specific achievement.
     * 
     * @param int $contestId
     * @param string $name
     * @param string $description
     * @return Achievement
     */
    public function createContestAchievement(int $contestId, string $name, string $description): Achievement
    {
        $definition = new ContestAchievement($contestId, $name, $description);
        
        return Achievement::updateOrCreate(
            ['key' => $definition->getKey()],
            $definition->toArray()
        );
    }

    /**
     * Evaluate contest achievement for a user.
     * 
     * @param User $user
     * @param int $contestId
     * @return bool True if achievement was newly unlocked
     */
    public function evaluateContestAchievement(User $user, int $contestId): bool
    {
        $achievement = Achievement::where('contest_id', $contestId)->first();
        
        if (!$achievement) {
            return false;
        }

        // Skip if already unlocked
        if ($user->hasAchievement($achievement->key)) {
            return false;
        }

        $definition = new ContestAchievement($contestId, $achievement->name, $achievement->description);
        
        if ($definition->isUnlocked($user)) {
            // Use firstOrCreate to handle race conditions gracefully
            $userAchievement = UserAchievement::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'achievement_id' => $achievement->id,
                ],
                [
                    'unlocked_at' => now(),
                ]
            );

            return $userAchievement->wasRecentlyCreated;
        }

        return false;
    }
}
