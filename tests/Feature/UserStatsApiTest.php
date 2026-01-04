<?php

use App\Models\User;
use App\Models\UserStats;

test('authenticated user can get their user stats', function () {
    $user = User::factory()->create();
    
    // Create user stats
    $userStats = UserStats::create([
        'user_id' => $user->id,
        'consistency_variance' => 12.5,
        'flash_work_ratio' => 0.35,
        'risk_profile_abandonment_rate' => 15.2,
        'avg_difficulty_abandoned' => 650.0,
        'long_routes_count' => 45,
        'short_routes_count' => 120,
        'avg_time_between_attempts' => 48.5,
        'movement_preferences' => ['crimpy' => 0.4, 'slopers' => 0.3],
        'preferred_climbing_hour' => '18:00',
        'avg_session_duration' => 2.5,
        'avg_routes_per_session' => 8.3,
        'exploration_ratio' => 65.5,
        'sector_fidelity' => ['sector_1' => 45, 'sector_2' => 30],
        'avg_attempts_before_success' => 2.8,
        'project_count' => 5,
        'progression_rate' => 1.2,
        'plateau_detected' => false,
        'plateau_weeks' => 0,
        'progression_by_style' => ['slab' => 0.8, 'overhang' => 1.5],
        'progression_by_sector' => ['sector_1' => 1.1, 'sector_2' => 0.9],
        'weekly_volume' => 120.5,
        'weekly_intensity' => 75.3,
        'acute_load' => 95.2,
        'chronic_load' => 88.7,
        'acute_chronic_ratio' => 1.07,
        'overtraining_detected' => false,
        'avg_recovery_time' => 72.0,
        'avg_time_between_performances' => 168.5,
        'last_calculated_at' => now(),
    ]);

    $response = $this->actingAs($user)->getJson('/api/v1/user/user-stats');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id',
            'user_id',
            'technical_analysis' => [
                'consistency_variance',
                'flash_work_ratio',
                'risk_profile_abandonment_rate',
                'avg_difficulty_abandoned',
                'long_routes_count',
                'short_routes_count',
                'avg_time_between_attempts',
                'movement_preferences',
            ],
            'behavioral_analysis' => [
                'preferred_climbing_hour',
                'avg_session_duration',
                'avg_routes_per_session',
                'exploration_ratio',
                'sector_fidelity',
                'avg_attempts_before_success',
                'project_count',
            ],
            'progression_analysis' => [
                'progression_rate',
                'plateau_detected',
                'plateau_weeks',
                'progression_by_style',
                'progression_by_sector',
            ],
            'training_load_analysis' => [
                'weekly_volume',
                'weekly_intensity',
                'acute_load',
                'chronic_load',
                'acute_chronic_ratio',
                'overtraining_detected',
                'avg_recovery_time',
                'avg_time_between_performances',
            ],
            'last_calculated_at',
            'created_at',
            'updated_at',
        ],
    ]);
    
    // Verify specific values
    $data = $response->json('data');
    expect($data['user_id'])->toBe($user->id);
    expect($data['technical_analysis']['consistency_variance'])->toBe(12.5);
    expect($data['behavioral_analysis']['preferred_climbing_hour'])->toBe('18:00');
    expect($data['progression_analysis']['plateau_detected'])->toBe(false);
    expect($data['training_load_analysis']['acute_chronic_ratio'])->toBe(1.07);
});

test('user stats endpoint returns 404 when no stats exist', function () {
    $user = User::factory()->create();
    
    // No stats created for this user
    $response = $this->actingAs($user)->getJson('/api/v1/user/user-stats');

    $response->assertStatus(404);
    $response->assertJson([
        'message' => 'Statistics not yet calculated. They will be available after the nightly update.',
        'data' => null,
    ]);
});

test('user stats endpoint requires authentication', function () {
    $response = $this->getJson('/api/v1/user/user-stats');

    $response->assertStatus(401);
});

test('user can only see their own stats', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    // Create stats for user2
    UserStats::create([
        'user_id' => $user2->id,
        'consistency_variance' => 10.0,
        'flash_work_ratio' => 0.5,
        'long_routes_count' => 50,
        'short_routes_count' => 100,
        'project_count' => 10,
        'plateau_detected' => false,
        'plateau_weeks' => 0,
        'overtraining_detected' => false,
        'last_calculated_at' => now(),
    ]);
    
    // User1 tries to access endpoint, should get 404 since they have no stats
    $response = $this->actingAs($user1)->getJson('/api/v1/user/user-stats');
    $response->assertStatus(404);
});

test('user stats response includes all categories', function () {
    $user = User::factory()->create();
    
    UserStats::create([
        'user_id' => $user->id,
        'consistency_variance' => 10.0,
        'flash_work_ratio' => 0.5,
        'plateau_detected' => false,
        'plateau_weeks' => 0,
        'overtraining_detected' => false,
        'last_calculated_at' => now(),
    ]);

    $response = $this->actingAs($user)->getJson('/api/v1/user/user-stats');

    $response->assertStatus(200);
    
    $data = $response->json('data');
    
    // Verify all major categories are present
    expect($data)->toHaveKeys([
        'technical_analysis',
        'behavioral_analysis',
        'progression_analysis',
        'training_load_analysis',
    ]);
});
