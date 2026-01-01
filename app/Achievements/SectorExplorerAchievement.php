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
        return 'Explorateur de la salle';
    }

    public function getDescription(): string
    {
        return 'Grimper au moins une voie dans chaque secteur de la salle';
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
