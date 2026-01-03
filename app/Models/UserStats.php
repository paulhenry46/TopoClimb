<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStats extends Model
{
    protected $fillable = [
        'user_id',
        'consistency_variance',
        'flash_work_ratio',
        'risk_profile_abandonment_rate',
        'avg_difficulty_abandoned',
        'long_routes_count',
        'short_routes_count',
        'avg_time_between_attempts',
        'movement_preferences',
        'preferred_climbing_hour',
        'avg_session_duration',
        'avg_routes_per_session',
        'exploration_ratio',
        'sector_fidelity',
        'avg_attempts_before_success',
        'project_count',
        'progression_rate',
        'plateau_detected',
        'plateau_weeks',
        'progression_by_style',
        'progression_by_sector',
        'weekly_volume',
        'weekly_intensity',
        'acute_load',
        'chronic_load',
        'acute_chronic_ratio',
        'overtraining_detected',
        'avg_recovery_time',
        'avg_time_between_performances',
        'last_calculated_at',
    ];

    protected $casts = [
        'movement_preferences' => 'array',
        'sector_fidelity' => 'array',
        'progression_by_style' => 'array',
        'progression_by_sector' => 'array',
        'plateau_detected' => 'boolean',
        'overtraining_detected' => 'boolean',
        'last_calculated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
