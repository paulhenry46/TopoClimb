<?php

use App\Models\User;
use App\Models\Achievement;
use App\Models\Route;
use App\Models\Log;
use App\Models\Line;
use App\Models\Sector;
use App\Models\Area;
use App\Models\Site;
use App\Services\AchievementService;

beforeEach(function () {
    // Create a test site structure
    $this->site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'lat' => 0,
        'lon' => 0,
        'cotation_type' => 'default',
    ]);

    $this->area = Area::create([
        'site_id' => $this->site->id,
        'name' => 'Test Area',
        'slug' => 'test-area',
    ]);

    $this->sector = Sector::create([
        'area_id' => $this->area->id,
        'name' => 'Test Sector',
        'slug' => 'test-sector',
    ]);

    $this->line = Line::create([
        'sector_id' => $this->sector->id,
        'name' => 'Test Line',
    ]);

    // Sync achievements
    $service = new AchievementService();
    $service->syncAchievements();
});

test('achievements are synced to database', function () {
    $achievements = Achievement::all();
    expect($achievements->count())->toBeGreaterThan(0);
});

test('max grade achievement is unlocked when user climbs required grade', function () {
    $user = User::factory()->create();
    
    // Create a route with grade 600 (6a)
    $route = Route::create([
        'line_id' => $this->line->id,
        'name' => 'Test Route',
        'slug' => 'test-route',
        'local_id' => 1,
        'grade' => 600,
        'color' => 'blue',
    ]);

    // Create a log for the user
    Log::create([
        'route_id' => $route->id,
        'user_id' => $user->id,
        'grade' => 600,
        'type' => 'work',
        'way' => 'lead',
    ]);

    // Evaluate achievements
    $service = new AchievementService();
    $newlyUnlocked = $service->evaluateAchievements($user);

    // Check if 6a achievement was unlocked
    expect($user->hasAchievement('max_grade_600'))->toBeTrue();
    expect($newlyUnlocked)->toContain('max_grade_600');
});

test('total routes achievement is unlocked when user climbs required count', function () {
    $user = User::factory()->create();

    // Create 10 routes and logs
    for ($i = 0; $i < 10; $i++) {
        $route = Route::create([
            'line_id' => $this->line->id,
            'name' => "Test Route $i",
            'slug' => "test-route-$i",
            'local_id' => $i + 1,
            'grade' => 500,
            'color' => 'blue',
        ]);

        Log::create([
            'route_id' => $route->id,
            'user_id' => $user->id,
            'grade' => 500,
            'type' => 'work',
            'way' => 'lead',
        ]);
    }

    // Evaluate achievements
    $service = new AchievementService();
    $service->evaluateAchievements($user);

    // Check if 10 routes achievement was unlocked
    expect($user->hasAchievement('total_routes_10'))->toBeTrue();
});

test('grade count achievement is unlocked when user climbs required count at grade', function () {
    $user = User::factory()->create();

    // Create 10 routes at grade 610 (6a+)
    for ($i = 0; $i < 10; $i++) {
        $route = Route::create([
            'line_id' => $this->line->id,
            'name' => "Test Route $i",
            'slug' => "test-route-$i",
            'local_id' => $i + 1,
            'grade' => 610,
            'color' => 'blue',
        ]);

        Log::create([
            'route_id' => $route->id,
            'user_id' => $user->id,
            'grade' => 610,
            'type' => 'work',
            'way' => 'lead',
        ]);
    }

    // Evaluate achievements
    $service = new AchievementService();
    $service->evaluateAchievements($user);

    // Check if 10 routes at 6a+ achievement was unlocked
    expect($user->hasAchievement('grade_count_610_10'))->toBeTrue();
});

test('achievement is not unlocked twice', function () {
    $user = User::factory()->create();
    
    // Create a route with grade 600 (6a)
    $route = Route::create([
        'line_id' => $this->line->id,
        'name' => 'Test Route',
        'slug' => 'test-route',
        'local_id' => 1,
        'grade' => 600,
        'color' => 'blue',
    ]);

    // Create a log for the user
    Log::create([
        'route_id' => $route->id,
        'user_id' => $user->id,
        'grade' => 600,
        'type' => 'work',
        'way' => 'lead',
    ]);

    // Evaluate achievements first time
    $service = new AchievementService();
    $firstUnlocked = $service->evaluateAchievements($user);

    // Evaluate achievements second time
    $secondUnlocked = $service->evaluateAchievements($user);

    // First time should unlock, second time should not
    expect($firstUnlocked)->toContain('max_grade_600');
    expect($secondUnlocked)->not->toContain('max_grade_600');
});

test('user can have multiple achievements', function () {
    $user = User::factory()->create();

    // Create routes at different grades
    for ($i = 0; $i < 15; $i++) {
        $grade = $i < 5 ? 500 : ($i < 10 ? 600 : 700);
        $route = Route::create([
            'line_id' => $this->line->id,
            'name' => "Test Route $i",
            'slug' => "test-route-$i",
            'local_id' => $i + 1,
            'grade' => $grade,
            'color' => 'blue',
        ]);

        Log::create([
            'route_id' => $route->id,
            'user_id' => $user->id,
            'grade' => $grade,
            'type' => 'work',
            'way' => 'lead',
        ]);
    }

    // Evaluate achievements
    $service = new AchievementService();
    $service->evaluateAchievements($user);

    // User should have multiple achievements
    $achievementCount = $user->achievements()->count();
    expect($achievementCount)->toBeGreaterThan(1);
});
