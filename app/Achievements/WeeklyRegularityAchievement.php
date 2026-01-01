<?php

namespace App\Achievements;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class WeeklyRegularityAchievement extends BaseAchievement
{
    private int $requiredWeeks;

    public function __construct(int $requiredWeeks)
    {
        $this->requiredWeeks = $requiredWeeks;
    }

    public function getKey(): string
    {
        return 'weekly_regularity_' . $this->requiredWeeks;
    }

    public function getName(): string
    {
        if ($this->requiredWeeks == 4) {
            return 'Régulier';
        } elseif ($this->requiredWeeks == 12) {
            return 'Jamais sans ma salle';
        }
        return 'Grimpeur régulier';
    }

    public function getDescription(): string
    {
        return 'Grimper au moins 1 fois par semaine pendant ' . $this->requiredWeeks . ' semaines consécutives';
    }

    public function getType(): string
    {
        return 'weekly_regularity';
    }

    public function getCriteria(): array
    {
        return [
            'required_weeks' => $this->requiredWeeks,
        ];
    }

    public function isUnlocked(User $user): bool
    {
        // Get all logs grouped by week
        $logs = DB::table('logs')
            ->where('user_id', $user->id)
            ->select(DB::raw('YEARWEEK(created_at, 1) as year_week'))
            ->distinct()
            ->orderBy('year_week')
            ->pluck('year_week')
            ->toArray();

        if (count($logs) < $this->requiredWeeks) {
            return false;
        }

        // Check for consecutive weeks
        $maxConsecutive = 1;
        $currentConsecutive = 1;

        for ($i = 1; $i < count($logs); $i++) {
            // Check if weeks are consecutive
            if ($logs[$i] == $logs[$i - 1] + 1) {
                $currentConsecutive++;
                $maxConsecutive = max($maxConsecutive, $currentConsecutive);
            } else {
                $currentConsecutive = 1;
            }
        }

        return $maxConsecutive >= $this->requiredWeeks;
    }
}
