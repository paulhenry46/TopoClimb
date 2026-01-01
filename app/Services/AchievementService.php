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

        // Max grade achievements
        $gradeDefinitions = [
            500 => '5a',
            540 => '5c',
            600 => '6a',
            610 => '6a+',
            620 => '6b',
            640 => '6c',
            700 => '7a',
            720 => '7b',
            800 => '8a',
        ];

        foreach ($gradeDefinitions as $grade => $label) {
            $achievements[] = new MaxGradeAchievement($grade, $label);
        }

        // Total routes achievements
        $routeCounts = [10, 25, 50, 100, 250, 500];
        foreach ($routeCounts as $count) {
            $achievements[] = new TotalRoutesAchievement($count);
        }

        // Grade count achievements
        $gradeCountDefinitions = [
            ['grade' => 610, 'count' => 10, 'label' => '6a+'],
            ['grade' => 620, 'count' => 10, 'label' => '6b'],
            ['grade' => 640, 'count' => 10, 'label' => '6c'],
            ['grade' => 700, 'count' => 5, 'label' => '7a'],
            ['grade' => 700, 'count' => 10, 'label' => '7a'],
        ];

        foreach ($gradeCountDefinitions as $def) {
            $achievements[] = new GradeCountAchievement($def['grade'], $def['count'], $def['label']);
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
