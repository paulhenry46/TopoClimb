<?php

namespace App\Achievements;

use App\Models\User;

abstract class BaseAchievement
{
    /**
     * Get the unique key for this achievement.
     */
    abstract public function getKey(): string;

    /**
     * Get the display name for this achievement (should be translated with __()).
     */
    abstract public function getName(): string;

    /**
     * Get the description for this achievement (should be translated with __()).
     */
    abstract public function getDescription(): string;

    /**
     * Get the type of this achievement.
     */
    abstract public function getType(): string;

    /**
     * Get the criteria for this achievement.
     */
    abstract public function getCriteria(): array;

    /**
     * Check if the user has unlocked this achievement.
     */
    abstract public function isUnlocked(User $user): bool;

    /**
     * Get the contest ID if this is a contest-specific achievement.
     */
    public function getContestId(): ?int
    {
        return null;
    }

    /**
     * Convert the achievement to an array for storage.
     */
    public function toArray(): array
    {
        return [
            'key' => $this->getKey(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'type' => $this->getType(),
            'criteria' => $this->getCriteria(),
            'contest_id' => $this->getContestId(),
        ];
    }
}
