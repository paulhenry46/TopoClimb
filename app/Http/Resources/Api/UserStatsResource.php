<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserStatsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            
            // Technical analysis metrics
            'technical_analysis' => [
                'consistency_variance' => $this->consistency_variance,
                'flash_work_ratio' => $this->flash_work_ratio,
                'risk_profile_abandonment_rate' => $this->risk_profile_abandonment_rate,
                'avg_difficulty_abandoned' => $this->avg_difficulty_abandoned,
                'long_routes_count' => $this->long_routes_count,
                'short_routes_count' => $this->short_routes_count,
                'avg_time_between_attempts' => $this->avg_time_between_attempts,
                'movement_preferences' => $this->movement_preferences,
            ],
            
            // Behavioral analysis metrics
            'behavioral_analysis' => [
                'preferred_climbing_hour' => $this->preferred_climbing_hour,
                'avg_session_duration' => $this->avg_session_duration,
                'avg_routes_per_session' => $this->avg_routes_per_session,
                'exploration_ratio' => $this->exploration_ratio,
                'sector_fidelity' => $this->sector_fidelity,
                'avg_attempts_before_success' => $this->avg_attempts_before_success,
                'project_count' => $this->project_count,
            ],
            
            // Progression analysis metrics
            'progression_analysis' => [
                'progression_rate' => $this->progression_rate,
                'plateau_detected' => $this->plateau_detected,
                'plateau_weeks' => $this->plateau_weeks,
                'progression_by_style' => $this->progression_by_style,
                'progression_by_sector' => $this->progression_by_sector,
            ],
            
            // Training load analysis metrics
            'training_load_analysis' => [
                'weekly_volume' => $this->weekly_volume,
                'weekly_intensity' => $this->weekly_intensity,
                'acute_load' => $this->acute_load,
                'chronic_load' => $this->chronic_load,
                'acute_chronic_ratio' => $this->acute_chronic_ratio,
                'overtraining_detected' => $this->overtraining_detected,
                'avg_recovery_time' => $this->avg_recovery_time,
                'avg_time_between_performances' => $this->avg_time_between_performances,
            ],
            
            'last_calculated_at' => $this->last_calculated_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
