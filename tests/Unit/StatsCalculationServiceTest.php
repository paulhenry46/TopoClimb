<?php

use App\Models\User;
use App\Models\Route;
use App\Models\Log;
use App\Models\UserStats;
use App\Services\StatsCalculationService;

test('stats are created for user with logs', function () {
    $user = User::factory()->create();
    $route = Route::factory()->create();
    
    // Create some logs
    Log::factory()->create([
        'user_id' => $user->id,
        'route_id' => $route->id,
        'type' => 'work',
        'grade' => 500,
        'is_public' => true,
    ]);

    $service = new StatsCalculationService();
    $service->calculateStatsForUser($user);

    expect($user->fresh()->stats)->not->toBeNull();
    expect($user->stats->last_calculated_at)->not->toBeNull();
});

test('tentative logs are included in stats calculations', function () {
    $user = User::factory()->create();
    $route = Route::factory()->create();
    
    // Create tentative log
    Log::factory()->create([
        'user_id' => $user->id,
        'route_id' => $route->id,
        'type' => 'tentative',
        'grade' => 600,
        'is_public' => false,
    ]);

    // Create success log
    Log::factory()->create([
        'user_id' => $user->id,
        'route_id' => $route->id,
        'type' => 'work',
        'grade' => 600,
        'is_public' => true,
    ]);

    $service = new StatsCalculationService();
    $service->calculateStatsForUser($user);

    // Stats should exist
    expect($user->fresh()->stats)->not->toBeNull();
});

test('flash work ratio is calculated correctly', function () {
    $user = User::factory()->create();
    
    // Create 2 flash logs
    for ($i = 0; $i < 2; $i++) {
        $route = Route::factory()->create();
        Log::factory()->create([
            'user_id' => $user->id,
            'route_id' => $route->id,
            'type' => 'flash',
            'grade' => 500,
            'is_public' => true,
        ]);
    }
    
    // Create 1 work log
    $route = Route::factory()->create();
    Log::factory()->create([
        'user_id' => $user->id,
        'route_id' => $route->id,
        'type' => 'work',
        'grade' => 500,
        'is_public' => true,
    ]);

    $service = new StatsCalculationService();
    $service->calculateStatsForUser($user);

    expect($user->fresh()->stats->flash_work_ratio)->toBe(2.0);
});
