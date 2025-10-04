<?php

use App\Models\Contest;
use App\Models\ContestCategory;
use App\Models\ContestStep;
use App\Models\Site;
use App\Models\Team;
use App\Models\User;
use App\Models\Area;
use App\Models\Route;
use App\Models\Log;

test('contest can have team mode enabled', function () {
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
        'team_mode' => true,
        'site_id' => $site->id,
    ]);

    expect($contest->team_mode)->toBeTrue();
});

test('teams can be created for a contest', function () {
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
        'team_mode' => true,
        'site_id' => $site->id,
    ]);

    $team = Team::create([
        'name' => 'Team Alpha',
        'contest_id' => $contest->id,
    ]);

    expect($contest->teams)->toHaveCount(1);
    expect($team->name)->toBe('Team Alpha');
});

test('users can join teams', function () {
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
        'team_mode' => true,
        'site_id' => $site->id,
    ]);

    $team = Team::create([
        'name' => 'Team Alpha',
        'contest_id' => $contest->id,
    ]);

    $user = User::factory()->create();
    $team->users()->attach($user->id);

    expect($team->users)->toHaveCount(1);
    expect($user->teams)->toHaveCount(1);
});

test('contest can have categories', function () {
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

    $category1 = ContestCategory::create([
        'name' => 'Men 18-25',
        'contest_id' => $contest->id,
        'type' => 'age',
        'criteria' => '18-25',
    ]);

    $category2 = ContestCategory::create([
        'name' => 'Women Elite',
        'contest_id' => $contest->id,
        'type' => 'gender',
        'criteria' => 'female',
    ]);

    expect($contest->categories)->toHaveCount(2);
    expect($category1->name)->toBe('Men 18-25');
    expect($category2->type)->toBe('gender');
});

test('users can join categories', function () {
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

    $category = ContestCategory::create([
        'name' => 'Men 18-25',
        'contest_id' => $contest->id,
        'type' => 'age',
        'criteria' => '18-25',
    ]);

    $user = User::factory()->create();
    $category->users()->attach($user->id);

    expect($category->users)->toHaveCount(1);
    expect($user->contestCategories)->toHaveCount(1);
});

test('contest steps can have specific routes', function () {
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

    $route1 = $line->routes()->create([
        'name' => 'Route 1',
        'slug' => 'route-1',
        'local_id' => 1,
        'grade' => 500,
        'color' => 'blue',
    ]);

    $route2 = $line->routes()->create([
        'name' => 'Route 2',
        'slug' => 'route-2',
        'local_id' => 2,
        'grade' => 600,
        'color' => 'red',
    ]);

    $step = ContestStep::create([
        'contest_id' => $contest->id,
        'name' => 'Qualification',
        'order' => 0,
        'start_time' => now(),
        'end_time' => now()->addHours(3),
    ]);

    $step->routes()->attach([$route1->id, $route2->id]);

    expect($step->routes)->toHaveCount(2);
});

test('team ranking calculates points correctly in unique mode', function () {
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
        'team_mode' => true,
        'team_points_mode' => 'unique',
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

    $route1 = $line->routes()->create([
        'name' => 'Route 1',
        'slug' => 'route-1',
        'local_id' => 1,
        'grade' => 500,
        'color' => 'blue',
    ]);

    $route2 = $line->routes()->create([
        'name' => 'Route 2',
        'slug' => 'route-2',
        'local_id' => 2,
        'grade' => 600,
        'color' => 'red',
    ]);

    $contest->routes()->attach($route1->id, ['points' => 100]);
    $contest->routes()->attach($route2->id, ['points' => 200]);

    $team = Team::create([
        'name' => 'Team Alpha',
        'contest_id' => $contest->id,
    ]);

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    
    $team->users()->attach([$user1->id, $user2->id]);

    // User 1 climbs route 1
    Log::create([
        'route_id' => $route1->id,
        'user_id' => $user1->id,
        'grade' => $route1->grade,
        'type' => 'flash',
        'way' => 'bouldering',
        'created_at' => now(),
    ]);

    // User 2 climbs route 2
    Log::create([
        'route_id' => $route2->id,
        'user_id' => $user2->id,
        'grade' => $route2->grade,
        'type' => 'flash',
        'way' => 'bouldering',
        'created_at' => now(),
    ]);

    $teamPoints = $team->getTotalPoints();
    expect($teamPoints)->toBe(300.0); // 100 + 200
});

test('team ranking calculates points correctly in all mode with duplicates', function () {
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
        'team_mode' => true,
        'team_points_mode' => 'all',
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

    $route1 = $line->routes()->create([
        'name' => 'Route 1',
        'slug' => 'route-1',
        'local_id' => 1,
        'grade' => 500,
        'color' => 'blue',
    ]);

    $route2 = $line->routes()->create([
        'name' => 'Route 2',
        'slug' => 'route-2',
        'local_id' => 2,
        'grade' => 600,
        'color' => 'red',
    ]);

    $contest->routes()->attach($route1->id, ['points' => 100]);
    $contest->routes()->attach($route2->id, ['points' => 200]);

    $team = Team::create([
        'name' => 'Team Beta',
        'contest_id' => $contest->id,
    ]);

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();
    
    $team->users()->attach([$user1->id, $user2->id, $user3->id]);

    // All 3 users climb route 1 (100 points each)
    Log::create([
        'route_id' => $route1->id,
        'user_id' => $user1->id,
        'grade' => $route1->grade,
        'type' => 'flash',
        'way' => 'bouldering',
        'created_at' => now(),
    ]);

    Log::create([
        'route_id' => $route1->id,
        'user_id' => $user2->id,
        'grade' => $route1->grade,
        'type' => 'flash',
        'way' => 'bouldering',
        'created_at' => now(),
    ]);

    Log::create([
        'route_id' => $route1->id,
        'user_id' => $user3->id,
        'grade' => $route1->grade,
        'type' => 'flash',
        'way' => 'bouldering',
        'created_at' => now(),
    ]);

    // 2 users climb route 2 (200 points each)
    Log::create([
        'route_id' => $route2->id,
        'user_id' => $user1->id,
        'grade' => $route2->grade,
        'type' => 'flash',
        'way' => 'bouldering',
        'created_at' => now(),
    ]);

    Log::create([
        'route_id' => $route2->id,
        'user_id' => $user2->id,
        'grade' => $route2->grade,
        'type' => 'flash',
        'way' => 'bouldering',
        'created_at' => now(),
    ]);

    $teamPoints = $team->getTotalPoints();
    // In 'all' mode: (3 × 100) + (2 × 200) = 300 + 400 = 700
    expect($teamPoints)->toBe(700.0);
});

test('category ranking filters users correctly', function () {
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
        'name' => 'Route 1',
        'slug' => 'route-1',
        'local_id' => 1,
        'grade' => 500,
        'color' => 'blue',
    ]);

    $contest->routes()->attach($route->id, ['points' => 100]);

    $category = ContestCategory::create([
        'name' => 'Men 18-25',
        'contest_id' => $contest->id,
        'type' => 'age',
        'criteria' => '18-25',
    ]);

    $user1 = User::factory()->create(['name' => 'User 1']);
    $user2 = User::factory()->create(['name' => 'User 2']);
    $user3 = User::factory()->create(['name' => 'User 3']);
    
    // Only user1 and user2 in the category
    $category->users()->attach([$user1->id, $user2->id]);

    // All users climb the route
    Log::create([
        'route_id' => $route->id,
        'user_id' => $user1->id,
        'grade' => $route->grade,
        'type' => 'flash',
        'way' => 'bouldering',
        'created_at' => now(),
    ]);

    Log::create([
        'route_id' => $route->id,
        'user_id' => $user2->id,
        'grade' => $route->grade,
        'type' => 'flash',
        'way' => 'bouldering',
        'created_at' => now(),
    ]);

    Log::create([
        'route_id' => $route->id,
        'user_id' => $user3->id,
        'grade' => $route->grade,
        'type' => 'flash',
        'way' => 'bouldering',
        'created_at' => now(),
    ]);

    $categoryRankings = $contest->getCategoryRankings($category->id);
    
    // Only users in category should appear
    expect($categoryRankings)->toHaveCount(2);
    expect($categoryRankings->pluck('user_id')->toArray())->toContain($user1->id);
    expect($categoryRankings->pluck('user_id')->toArray())->toContain($user2->id);
    expect($categoryRankings->pluck('user_id')->toArray())->not->toContain($user3->id);
});
