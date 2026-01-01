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
        'address' => '123 Test Street',
        'cotation_type' => 'default',
    ]);

    $this->area = Area::create([
        'site_id' => $this->site->id,
        'name' => 'Test Area',
        'slug' => 'test-area',
        'type' => 'bouldering',
    ]);

    $this->sector = Sector::create([
        'area_id' => $this->area->id,
        'name' => 'Test Sector',
        'slug' => 'test-sector',
        'local_id' => 1,
    ]);

    $this->line = Line::create([
        'sector_id' => $this->sector->id,
        'name' => 'Test Line',
        'local_id' => 1,
    ]);

    // Sync achievements
    $service = new AchievementService();
    $service->syncAchievements();
});

test('can get all achievements via public API endpoint', function () {
    $response = $this->getJson('/api/v1/achievements');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'key',
                'name',
                'description',
                'type',
                'criteria',
                'contest_id',
                'created_at',
                'updated_at',
            ]
        ]
    ]);
    
    // Verify we get all achievements
    $achievementsCount = Achievement::count();
    expect(count($response->json('data')))->toBe($achievementsCount);
});

test('achievements endpoint returns correct data structure', function () {
    $response = $this->getJson('/api/v1/achievements');

    $response->assertStatus(200);
    
    // Check that the response contains expected achievements
    $data = $response->json('data');
    expect($data)->toBeArray();
    expect(count($data))->toBeGreaterThan(0);
    
    // Verify first achievement has all required fields
    $firstAchievement = $data[0];
    expect($firstAchievement)->toHaveKeys(['id', 'key', 'name', 'description', 'type', 'criteria']);
});

test('authenticated user can get their unlocked achievement IDs', function () {
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

    // Create a log for the user (this will auto-unlock achievements via observer)
    Log::create([
        'route_id' => $route->id,
        'user_id' => $user->id,
        'grade' => 600,
        'type' => 'work',
        'way' => 'lead',
    ]);

    $response = $this->actingAs($user)->getJson('/api/v1/user/achievements');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data'
    ]);
    
    // Verify the data is an array of achievement IDs
    $data = $response->json('data');
    expect($data)->toBeArray();
    expect(count($data))->toBeGreaterThan(0);
    
    // All items should be integers (achievement IDs)
    foreach ($data as $id) {
        expect($id)->toBeInt();
    }
});

test('user achievements endpoint returns correct achievement IDs', function () {
    $user = User::factory()->create();
    
    // Create multiple routes and logs to unlock several achievements
    for ($i = 0; $i < 10; $i++) {
        $route = Route::create([
            'line_id' => $this->line->id,
            'name' => "Test Route $i",
            'slug' => "test-route-$i",
            'local_id' => $i + 1,
            'grade' => 600,
            'color' => 'blue',
        ]);

        Log::create([
            'route_id' => $route->id,
            'user_id' => $user->id,
            'grade' => 600,
            'type' => 'work',
            'way' => 'lead',
        ]);
    }

    $response = $this->actingAs($user)->getJson('/api/v1/user/achievements');

    $response->assertStatus(200);
    
    $achievementIds = $response->json('data');
    expect($achievementIds)->toBeArray();
    
    // User should have unlocked the max_grade_600 and total_routes_10 achievements at minimum
    $user->refresh();
    $userAchievementIds = $user->achievements()->pluck('achievements.id')->toArray();
    
    // The API response should match the user's actual achievements
    expect($achievementIds)->toEqual($userAchievementIds);
});

test('user achievements endpoint requires authentication', function () {
    $response = $this->getJson('/api/v1/user/achievements');

    $response->assertStatus(401);
});

test('user with no achievements returns empty array', function () {
    $user = User::factory()->create();
    
    // User has no logs, so no achievements should be unlocked
    $response = $this->actingAs($user)->getJson('/api/v1/user/achievements');

    $response->assertStatus(200);
    $response->assertJson([
        'data' => []
    ]);
});
