<?php

use App\Models\User;
use App\Models\Route;
use App\Models\Log;

test('tentative logs are marked as private', function () {
    $user = User::factory()->create();
    $route = Route::factory()->create();
    
    $log = Log::factory()->create([
        'user_id' => $user->id,
        'route_id' => $route->id,
        'type' => 'tentative',
        'is_public' => false,
    ]);

    expect($log->is_public)->toBeFalse();
    expect($log->type)->toBe('tentative');
});

test('public logs are marked as public', function () {
    $user = User::factory()->create();
    $route = Route::factory()->create();
    
    $types = ['work', 'flash', 'view'];
    
    foreach ($types as $type) {
        $log = Log::factory()->create([
            'user_id' => $user->id,
            'route_id' => $route->id,
            'type' => $type,
            'is_public' => true,
        ]);

        expect($log->is_public)->toBeTrue();
    }
});

test('public scope filters tentative logs', function () {
    $user = User::factory()->create();
    $route1 = Route::factory()->create();
    $route2 = Route::factory()->create();
    
    // Create public log
    Log::factory()->create([
        'user_id' => $user->id,
        'route_id' => $route1->id,
        'type' => 'work',
        'is_public' => true,
    ]);
    
    // Create tentative log
    Log::factory()->create([
        'user_id' => $user->id,
        'route_id' => $route2->id,
        'type' => 'tentative',
        'is_public' => false,
    ]);

    $publicLogs = Log::public()->get();
    
    expect($publicLogs)->toHaveCount(1);
    expect($publicLogs->first()->type)->toBe('work');
});

test('tentative scope filters only tentative logs', function () {
    $user = User::factory()->create();
    $route1 = Route::factory()->create();
    $route2 = Route::factory()->create();
    
    // Create public log
    Log::factory()->create([
        'user_id' => $user->id,
        'route_id' => $route1->id,
        'type' => 'work',
        'is_public' => true,
    ]);
    
    // Create tentative log
    Log::factory()->create([
        'user_id' => $user->id,
        'route_id' => $route2->id,
        'type' => 'tentative',
        'is_public' => false,
    ]);

    $tentativeLogs = Log::tentative()->get();
    
    expect($tentativeLogs)->toHaveCount(1);
    expect($tentativeLogs->first()->type)->toBe('tentative');
});

test('multiple tentative logs allowed for same route', function () {
    $user = User::factory()->create();
    $route = Route::factory()->create();
    
    // Create multiple tentative logs for same route
    Log::factory()->create([
        'user_id' => $user->id,
        'route_id' => $route->id,
        'type' => 'tentative',
        'is_public' => false,
        'way' => 'top-rope',
    ]);
    
    Log::factory()->create([
        'user_id' => $user->id,
        'route_id' => $route->id,
        'type' => 'tentative',
        'is_public' => false,
        'way' => 'top-rope',
    ]);

    $logs = Log::where('user_id', $user->id)
        ->where('route_id', $route->id)
        ->where('type', 'tentative')
        ->get();
    
    expect($logs)->toHaveCount(2);
});
