<?php

use App\Models\Area;
use App\Models\Contest;
use App\Models\Line;
use App\Models\Log;
use App\Models\Route;
use App\Models\Sector;
use App\Models\Site;
use App\Models\User;

test('can get public user profile with stats', function () {
    $site = Site::factory()->create();
    $area = Area::factory()->create(['site_id' => $site->id, 'type' => 'bouldering']);
    $sector = Sector::factory()->create(['area_id' => $area->id]);
    $line = Line::factory()->create(['sector_id' => $sector->id]);
    $route = Route::factory()->create(['line_id' => $line->id, 'grade' => 600]);

    $user = User::factory()->create(['name' => 'Test User']);

    // Create a log for this user
    Log::create([
        'route_id' => $route->id,
        'user_id' => $user->id,
        'grade' => 600,
        'type' => 'flash',
        'way' => 'bouldering',
    ]);

    $response = $this->getJson("/api/v1/users/{$user->id}");

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id',
            'name',
            'profile_photo_url',
            'stats' => [
                'trad_level',
                'bouldering_level',
                'total_climbed',
                'routes_by_grade',
            ],
        ],
    ]);
    $response->assertJsonPath('data.name', 'Test User');
    $response->assertJsonPath('data.stats.total_climbed', 1);
});

test('can get public user profile without any logs', function () {
    $user = User::factory()->create(['name' => 'New User']);

    $response = $this->getJson("/api/v1/users/{$user->id}");

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id',
            'name',
            'profile_photo_url',
            'stats' => [
                'trad_level',
                'bouldering_level',
                'total_climbed',
                'routes_by_grade',
            ],
        ],
    ]);
    $response->assertJsonPath('data.stats.total_climbed', 0);
});

test('returns 404 for non-existent user', function () {
    $response = $this->getJson('/api/v1/users/99999');

    $response->assertStatus(404);
});

test('can get last routes logged by user', function () {
    $site = Site::factory()->create();
    $area = Area::factory()->create(['site_id' => $site->id, 'type' => 'bouldering']);
    $sector = Sector::factory()->create(['area_id' => $area->id]);
    $line = Line::factory()->create(['sector_id' => $sector->id]);
    $route1 = Route::factory()->create(['line_id' => $line->id]);
    $route2 = Route::factory()->create(['line_id' => $line->id]);

    $user = User::factory()->create();

    // Create logs
    Log::create([
        'route_id' => $route1->id,
        'user_id' => $user->id,
        'grade' => 600,
        'type' => 'flash',
        'way' => 'bouldering',
    ]);
    Log::create([
        'route_id' => $route2->id,
        'user_id' => $user->id,
        'grade' => 650,
        'type' => 'work',
        'way' => 'bouldering',
    ]);

    $response = $this->getJson("/api/v1/users/{$user->id}/routes");

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'route_id',
                'type',
                'way',
                'grade',
            ],
        ],
    ]);
    // Should return at most 3 logs
    expect(count($response->json('data')))->toBeLessThanOrEqual(3);
});

test('user routes endpoint returns max 3 logs', function () {
    $site = Site::factory()->create();
    $area = Area::factory()->create(['site_id' => $site->id, 'type' => 'bouldering']);
    $sector = Sector::factory()->create(['area_id' => $area->id]);
    $line = Line::factory()->create(['sector_id' => $sector->id]);

    $user = User::factory()->create();

    // Create 5 logs
    for ($i = 0; $i < 5; $i++) {
        $route = Route::factory()->create(['line_id' => $line->id]);
        Log::create([
            'route_id' => $route->id,
            'user_id' => $user->id,
            'grade' => 600 + ($i * 10),
            'type' => 'flash',
            'way' => 'bouldering',
        ]);
    }

    $response = $this->getJson("/api/v1/users/{$user->id}/routes");

    $response->assertStatus(200);
    // Should return exactly 3 logs (max)
    expect(count($response->json('data')))->toBe(3);
});

test('can get current events', function () {
    $site = Site::factory()->create();

    // Create an ongoing contest
    $ongoingContest = Contest::factory()->create([
        'site_id' => $site->id,
        'start_date' => now()->subDay(),
        'end_date' => now()->addDay(),
        'name' => 'Ongoing Contest',
        'team_mode' => null,
    ]);

    // Create a future contest
    Contest::factory()->create([
        'site_id' => $site->id,
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(10),
        'name' => 'Future Contest',
        'team_mode' => null,
    ]);

    // Create a past contest
    Contest::factory()->create([
        'site_id' => $site->id,
        'start_date' => now()->subDays(10),
        'end_date' => now()->subDays(5),
        'name' => 'Past Contest',
        'team_mode' => null,
    ]);

    $response = $this->getJson('/api/v1/current_events');

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'name',
                'description',
                'start_date',
                'end_date',
            ],
        ],
    ]);
    // Should only contain the ongoing contest
    expect(count($response->json('data')))->toBe(1);
    $response->assertJsonPath('data.0.name', 'Ongoing Contest');
});

test('current events returns empty when no ongoing contests', function () {
    $site = Site::factory()->create();

    // Create only future contest
    Contest::factory()->create([
        'site_id' => $site->id,
        'start_date' => now()->addDays(5),
        'end_date' => now()->addDays(10),
        'team_mode' => null,
    ]);

    $response = $this->getJson('/api/v1/current_events');

    $response->assertStatus(200);
    expect(count($response->json('data')))->toBe(0);
});
