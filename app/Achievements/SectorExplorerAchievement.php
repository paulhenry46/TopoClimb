<?php

namespace App\Achievements;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class SectorExplorerAchievement extends BaseAchievement
{
    private int $siteId;

    public function __construct(int $siteId)
    {
        $this->siteId = $siteId;
    }

    public function getKey(): string
    {
        return 'sector_explorer_' . $this->siteId;
    }

    public function getName(): string
    {
        return __('Gym explorer');
    }

    public function getDescription(): string
    {
        return __('Climb at least one route in every sector of the gym');
    }

    public function getType(): string
    {
        return 'sector_explorer';
    }

    public function getCriteria(): array
    {
        return [
            'site_id' => $this->siteId,
        ];
    }

    public function isUnlocked(User $user): bool
    {
        // Count total sectors in the site
        $totalSectors = DB::table('sectors')
            ->join('areas', 'sectors.area_id', '=', 'areas.id')
            ->where('areas.site_id', $this->siteId)
            ->count();

        if ($totalSectors == 0) {
            return false;
        }

        // Count distinct sectors climbed by user
        $climbedSectors = DB::table('logs')
            ->join('routes', 'logs.route_id', '=', 'routes.id')
            ->join('lines', 'routes.line_id', '=', 'lines.id')
            ->join('sectors', 'lines.sector_id', '=', 'sectors.id')
            ->join('areas', 'sectors.area_id', '=', 'areas.id')
            ->where('logs.user_id', $user->id)
            ->where('areas.site_id', $this->siteId)
            ->distinct('sectors.id')
            ->count('sectors.id');

        return $climbedSectors >= $totalSectors;
    }
}
