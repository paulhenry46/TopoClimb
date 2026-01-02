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
            return __('Consistent');
        } elseif ($this->requiredWeeks == 12) {
            return __('Never miss my gym');
        }
        return __('Regular climber');
    }

    public function getDescription(): string
    {
        return __('Climb at least once a week for :weeks consecutive weeks', ['weeks' => $this->requiredWeeks]);
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
        // Get all logs and group by ISO year and week in PHP
        $logs = $user->logs()
            ->orderBy('created_at')
            ->get()
            ->map(function ($log) {
                $date = $log->created_at instanceof \Carbon\Carbon ? $log->created_at : \Carbon\Carbon::parse($log->created_at);
                return $date->isoFormat('GGGG-ww'); // ISO year-week
            })
            ->unique()
            ->values()
            ->toArray();

        if (count($logs) < $this->requiredWeeks) {
            return false;
        }

        // Convert year-week strings to comparable integers for consecutive check
        $weeks = array_map(function ($yearWeek) {
            [$year, $week] = explode('-', $yearWeek);
            return ((int)$year) * 100 + (int)$week;
        }, $logs);

        $maxConsecutive = 1;
        $currentConsecutive = 1;

        for ($i = 1; $i < count($weeks); $i++) {
            if ($weeks[$i] == $weeks[$i - 1] + 1) {
                $currentConsecutive++;
                $maxConsecutive = max($maxConsecutive, $currentConsecutive);
            } else {
                $currentConsecutive = 1;
            }
        }

        return $maxConsecutive >= $this->requiredWeeks;
    }
}
