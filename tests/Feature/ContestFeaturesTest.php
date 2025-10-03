<?php

use App\Models\Contest;
use App\Models\ContestStep;
use App\Models\Log;
use App\Models\Route;
use App\Models\Site;
use App\Models\User;

test('contest route can have points in pivot table', function () {
    $site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);

    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test Description',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'free',
        'site_id' => $site->id,
    ]);

    $area = $site->areas()->create([
        'name' => 'Test Area',
        'slug' => 'test-area',
        'type' => 'bouldering',
    ]);

    $sector = $area->sectors()->create([
        'name' => 'Test Sector',
        'slug' => 'test-sector',
        'local_id' => 1,
    ]);

    $line = $sector->lines()->create([
        'local_id' => 1,
    ]);

    $route = $line->routes()->create([
        'name' => 'Test Route',
        'slug' => 'test-route',
        'local_id' => 1,
        'grade' => 500,
        'color' => 'blue',
    ]);

    // Attach route with custom points
    $contest->routes()->attach($route->id, ['points' => 150]);

    $contestRoute = $contest->routes()->first();
    expect($contestRoute->pivot->points)->toBe(150);
});

test('contest route points default to 100', function () {
    $site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);

    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test Description',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'free',
        'site_id' => $site->id,
    ]);

    $area = $site->areas()->create([
        'name' => 'Test Area',
        'slug' => 'test-area',
        'type' => 'bouldering',
    ]);

    $sector = $area->sectors()->create([
        'name' => 'Test Sector',
        'slug' => 'test-sector',
        'local_id' => 1,
    ]);

    $line = $sector->lines()->create([
        'local_id' => 1,
    ]);

    $route = $line->routes()->create([
        'name' => 'Test Route',
        'slug' => 'test-route',
        'local_id' => 1,
        'grade' => 500,
        'color' => 'blue',
    ]);

    // Attach route without specifying points
    $contest->routes()->attach($route->id);

    $contestRoute = $contest->routes()->first();
    expect($contestRoute->pivot->points)->toBe(100);
});

test('contest can have steps', function () {
    $site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);

    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test Description',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'free',
        'site_id' => $site->id,
    ]);

    $step1 = ContestStep::create([
        'contest_id' => $contest->id,
        'name' => 'Pre-qualification Wave 1',
        'order' => 0,
        'start_time' => now(),
        'end_time' => now()->addHours(3),
    ]);

    $step2 = ContestStep::create([
        'contest_id' => $contest->id,
        'name' => 'Final',
        'order' => 1,
        'start_time' => now()->addHours(4),
        'end_time' => now()->addHours(6),
    ]);

    expect($contest->steps)->toHaveCount(2);
    expect($contest->steps->first()->name)->toBe('Pre-qualification Wave 1');
    expect($contest->steps->last()->name)->toBe('Final');
});

test('contest step status methods work correctly', function () {
    $site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);

    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test Description',
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(7),
        'mode' => 'free',
        'site_id' => $site->id,
    ]);

    // Active step
    $activeStep = ContestStep::create([
        'contest_id' => $contest->id,
        'name' => 'Active Step',
        'order' => 0,
        'start_time' => now()->subHour(),
        'end_time' => now()->addHour(),
    ]);

    expect($activeStep->isActive())->toBeTrue();
    expect($activeStep->isPast())->toBeFalse();
    expect($activeStep->isFuture())->toBeFalse();

    // Future step
    $futureStep = ContestStep::create([
        'contest_id' => $contest->id,
        'name' => 'Future Step',
        'order' => 1,
        'start_time' => now()->addHours(2),
        'end_time' => now()->addHours(4),
    ]);

    expect($futureStep->isActive())->toBeFalse();
    expect($futureStep->isPast())->toBeFalse();
    expect($futureStep->isFuture())->toBeTrue();

    // Past step
    $pastStep = ContestStep::create([
        'contest_id' => $contest->id,
        'name' => 'Past Step',
        'order' => 2,
        'start_time' => now()->subHours(4),
        'end_time' => now()->subHours(2),
    ]);

    expect($pastStep->isActive())->toBeFalse();
    expect($pastStep->isPast())->toBeTrue();
    expect($pastStep->isFuture())->toBeFalse();
});

test('dynamic points calculation divides base points by climbers count', function () {
    $site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);

    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test Description',
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(7),
        'mode' => 'official',
        'use_dynamic_points' => true,
        'site_id' => $site->id,
    ]);

    $area = $site->areas()->create([
        'name' => 'Test Area',
        'slug' => 'test-area',
        'type' => 'bouldering',
    ]);

    $sector = $area->sectors()->create([
        'name' => 'Test Sector',
        'slug' => 'test-sector',
        'local_id' => 1,
    ]);

    $line = $sector->lines()->create([
        'local_id' => 1,
    ]);

    $route = $line->routes()->create([
        'name' => 'Test Route',
        'slug' => 'test-route',
        'local_id' => 1,
        'grade' => 500,
        'color' => 'blue',
    ]);

    // Attach route with 300 points
    $contest->routes()->attach($route->id, ['points' => 300]);

    // No climbers yet, should return full points
    expect($contest->getRoutePoints($route->id))->toBe(300.0);

    // Add 3 climbers who completed it
    $staff = User::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    Log::create([
        'route_id' => $route->id,
        'user_id' => $user1->id,
        'grade' => $route->grade,
        'type' => 'flash',
        'way' => 'bouldering',
        'verified_by' => $staff->id,
        'created_at' => now(),
    ]);

    Log::create([
        'route_id' => $route->id,
        'user_id' => $user2->id,
        'grade' => $route->grade,
        'type' => 'flash',
        'way' => 'bouldering',
        'verified_by' => $staff->id,
        'created_at' => now(),
    ]);

    Log::create([
        'route_id' => $route->id,
        'user_id' => $user3->id,
        'grade' => $route->grade,
        'type' => 'flash',
        'way' => 'bouldering',
        'verified_by' => $staff->id,
        'created_at' => now(),
    ]);

    // 300 points / 3 climbers = 100 points
    expect($contest->getRoutePoints($route->id))->toBe(100.0);
});

test('dynamic points calculation returns base points when no climbers', function () {
    $site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);

    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test Description',
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(7),
        'mode' => 'official',
        'use_dynamic_points' => true,
        'site_id' => $site->id,
    ]);

    $area = $site->areas()->create([
        'name' => 'Test Area',
        'slug' => 'test-area',
        'type' => 'bouldering',
    ]);

    $sector = $area->sectors()->create([
        'name' => 'Test Sector',
        'slug' => 'test-sector',
        'local_id' => 1,
    ]);

    $line = $sector->lines()->create([
        'local_id' => 1,
    ]);

    $route = $line->routes()->create([
        'name' => 'Test Route',
        'slug' => 'test-route',
        'local_id' => 1,
        'grade' => 500,
        'color' => 'blue',
    ]);

    // Attach route with 200 points
    $contest->routes()->attach($route->id, ['points' => 200]);

    // No climbers, should return base points
    expect($contest->getRoutePoints($route->id))->toBe(200.0);
});

test('dynamic points disabled returns base points regardless of climbers', function () {
    $site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);

    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test Description',
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(7),
        'mode' => 'official',
        'use_dynamic_points' => false,
        'site_id' => $site->id,
    ]);

    $area = $site->areas()->create([
        'name' => 'Test Area',
        'slug' => 'test-area',
        'type' => 'bouldering',
    ]);

    $sector = $area->sectors()->create([
        'name' => 'Test Sector',
        'slug' => 'test-sector',
        'local_id' => 1,
    ]);

    $line = $sector->lines()->create([
        'local_id' => 1,
    ]);

    $route = $line->routes()->create([
        'name' => 'Test Route',
        'slug' => 'test-route',
        'local_id' => 1,
        'grade' => 500,
        'color' => 'blue',
    ]);

    // Attach route with 300 points
    $contest->routes()->attach($route->id, ['points' => 300]);

    // Add climbers
    $staff = User::factory()->create();
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    Log::create([
        'route_id' => $route->id,
        'user_id' => $user1->id,
        'grade' => $route->grade,
        'type' => 'flash',
        'way' => 'bouldering',
        'verified_by' => $staff->id,
        'created_at' => now(),
    ]);

    Log::create([
        'route_id' => $route->id,
        'user_id' => $user2->id,
        'grade' => $route->grade,
        'type' => 'flash',
        'way' => 'bouldering',
        'verified_by' => $staff->id,
        'created_at' => now(),
    ]);

    // Even with climbers, should return base points because dynamic is disabled
    expect($contest->getRoutePoints($route->id))->toBe(300.0);
});

test('free mode counts all logs for dynamic points', function () {
    $site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);

    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test Description',
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(7),
        'mode' => 'free',
        'use_dynamic_points' => true,
        'site_id' => $site->id,
    ]);

    $area = $site->areas()->create([
        'name' => 'Test Area',
        'slug' => 'test-area',
        'type' => 'bouldering',
    ]);

    $sector = $area->sectors()->create([
        'name' => 'Test Sector',
        'slug' => 'test-sector',
        'local_id' => 1,
    ]);

    $line = $sector->lines()->create([
        'local_id' => 1,
    ]);

    $route = $line->routes()->create([
        'name' => 'Test Route',
        'slug' => 'test-route',
        'local_id' => 1,
        'grade' => 500,
        'color' => 'blue',
    ]);

    // Attach route with 300 points
    $contest->routes()->attach($route->id, ['points' => 300]);

    // Add 2 climbers - one with verified log, one without
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $staff = User::factory()->create();

    // Verified log
    Log::create([
        'route_id' => $route->id,
        'user_id' => $user1->id,
        'grade' => $route->grade,
        'type' => 'flash',
        'way' => 'bouldering',
        'verified_by' => $staff->id,
        'created_at' => now(),
    ]);

    // Unverified log - should also count in free mode
    Log::create([
        'route_id' => $route->id,
        'user_id' => $user2->id,
        'grade' => $route->grade,
        'type' => 'flash',
        'way' => 'bouldering',
        'verified_by' => null,
        'created_at' => now(),
    ]);

    // 300 points / 2 climbers (both verified and unverified) = 150 points
    expect($contest->getRoutePoints($route->id))->toBe(150.0);
});

test('official mode counts only verified logs for dynamic points', function () {
    $site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);

    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test Description',
        'start_date' => now()->subDay(),
        'end_date' => now()->addDays(7),
        'mode' => 'official',
        'use_dynamic_points' => true,
        'site_id' => $site->id,
    ]);

    $area = $site->areas()->create([
        'name' => 'Test Area',
        'slug' => 'test-area',
        'type' => 'bouldering',
    ]);

    $sector = $area->sectors()->create([
        'name' => 'Test Sector',
        'slug' => 'test-sector',
        'local_id' => 1,
    ]);

    $line = $sector->lines()->create([
        'local_id' => 1,
    ]);

    $route = $line->routes()->create([
        'name' => 'Test Route',
        'slug' => 'test-route',
        'local_id' => 1,
        'grade' => 500,
        'color' => 'blue',
    ]);

    // Attach route with 300 points
    $contest->routes()->attach($route->id, ['points' => 300]);

    // Add 2 climbers - one with verified log, one without
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $staff = User::factory()->create();

    // Verified log
    Log::create([
        'route_id' => $route->id,
        'user_id' => $user1->id,
        'grade' => $route->grade,
        'type' => 'flash',
        'way' => 'bouldering',
        'verified_by' => $staff->id,
        'created_at' => now(),
    ]);

    // Unverified log - should NOT count in official mode
    Log::create([
        'route_id' => $route->id,
        'user_id' => $user2->id,
        'grade' => $route->grade,
        'type' => 'flash',
        'way' => 'bouldering',
        'verified_by' => null,
        'created_at' => now(),
    ]);

    // 300 points / 1 climber (only verified) = 300 points
    expect($contest->getRoutePoints($route->id))->toBe(300.0);
});
