<?php

use App\Models\Contest;
use App\Models\ContestCategory;
use App\Models\Log;
use App\Models\Route;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);

    $this->contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test Description',
        'start_date' => now()->subDay(),
        'end_date' => now()->addDay(),
        'mode' => 'free',
        'site_id' => $this->site->id,
    ]);
});

test('can get categories for a contest', function () {
    $category1 = ContestCategory::create([
        'name' => 'Category 1',
        'contest_id' => $this->contest->id,
        'auto_assign' => false,
    ]);

    $category2 = ContestCategory::create([
        'name' => 'Category 2',
        'contest_id' => $this->contest->id,
        'auto_assign' => true,
    ]);

    $response = $this->getJson("/api/v1/contests/{$this->contest->id}/categories");

    $response->assertStatus(200);
    $response->assertJsonCount(2, 'data');
    $response->assertJsonFragment(['name' => 'Category 1']);
    $response->assertJsonFragment(['name' => 'Category 2']);
});

test('returns empty array when contest has no categories', function () {
    $response = $this->getJson("/api/v1/contests/{$this->contest->id}/categories");

    $response->assertStatus(200);
    $response->assertJsonCount(0, 'data');
});

test('can get category rank for contest', function () {
    $category = ContestCategory::create([
        'name' => 'Test Category',
        'contest_id' => $this->contest->id,
        'auto_assign' => false,
    ]);

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $category->users()->attach([$user1->id, $user2->id]);

    // Create area, sector, line, and routes
    $area = $this->site->areas()->create([
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

    // Attach routes to contest main step
    $mainStep = $this->contest->mainStep();
    $mainStep->routes()->attach($route1->id, ['points' => 100]);
    $mainStep->routes()->attach($route2->id, ['points' => 150]);

    // Create logs for users in category
    Log::create([
        'route_id' => $route1->id,
        'user_id' => $user1->id,
        'grade' => $route1->grade,
        'type' => 'flash',
        'way' => 'bouldering',
    ]);

    Log::create([
        'route_id' => $route2->id,
        'user_id' => $user1->id,
        'grade' => $route2->grade,
        'type' => 'flash',
        'way' => 'bouldering',
    ]);

    Log::create([
        'route_id' => $route1->id,
        'user_id' => $user2->id,
        'grade' => $route1->grade,
        'type' => 'flash',
        'way' => 'bouldering',
    ]);

    // Create log for user not in category (should not appear in category ranking)
    Log::create([
        'route_id' => $route1->id,
        'user_id' => $user3->id,
        'grade' => $route1->grade,
        'type' => 'flash',
        'way' => 'bouldering',
    ]);

    $response = $this->getJson("/api/v1/contests/{$this->contest->id}/categories/{$category->id}/rank");

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'rank' => [
            '*' => [
                'user_id',
                'user_name',
                'routes_count',
                'total_points',
                'rank',
            ],
        ],
    ]);

    // Should only have 2 users (user1 and user2, not user3)
    expect($response->json('rank'))->toHaveCount(2);

    // User1 should be ranked first (2 routes)
    expect($response->json('rank.0.user_id'))->toBe($user1->id);
    expect($response->json('rank.0.rank'))->toBe(1);

    // User2 should be ranked second (1 route)
    expect($response->json('rank.1.user_id'))->toBe($user2->id);
    expect($response->json('rank.1.rank'))->toBe(2);
});

test('can get category rank for contest step', function () {
    $category = ContestCategory::create([
        'name' => 'Test Category',
        'contest_id' => $this->contest->id,
        'auto_assign' => false,
    ]);

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();

    $category->users()->attach([$user1->id, $user2->id]);

    // Create area, sector, line, and route
    $area = $this->site->areas()->create([
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

    // Get main step
    $mainStep = $this->contest->mainStep();
    $mainStep->routes()->attach($route->id, ['points' => 100]);

    // Create log for user1
    Log::create([
        'route_id' => $route->id,
        'user_id' => $user1->id,
        'grade' => $route->grade,
        'type' => 'flash',
        'way' => 'bouldering',
    ]);

    $response = $this->getJson("/api/v1/contests/{$this->contest->id}/categories/{$category->id}/steps/{$mainStep->id}/rank");

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'rank' => [
            '*' => [
                'user_id',
                'user_name',
                'routes_count',
                'total_points',
                'rank',
            ],
        ],
    ]);

    expect($response->json('rank'))->toHaveCount(1);
    expect($response->json('rank.0.user_id'))->toBe($user1->id);
});

test('category rank returns error if category does not belong to contest', function () {
    $otherContest = Contest::create([
        'name' => 'Other Contest',
        'description' => 'Other Description',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'free',
        'site_id' => $this->site->id,
    ]);

    $category = ContestCategory::create([
        'name' => 'Test Category',
        'contest_id' => $otherContest->id,
        'auto_assign' => false,
    ]);

    $response = $this->getJson("/api/v1/contests/{$this->contest->id}/categories/{$category->id}/rank");

    $response->assertStatus(404);
    $response->assertJson(['error' => 'Category does not belong to this contest']);
});

test('authenticated user can get their categories for a contest', function () {
    $user = User::factory()->create();

    $category1 = ContestCategory::create([
        'name' => 'Category 1',
        'contest_id' => $this->contest->id,
        'auto_assign' => false,
    ]);

    $category2 = ContestCategory::create([
        'name' => 'Category 2',
        'contest_id' => $this->contest->id,
        'auto_assign' => false,
    ]);

    $category3 = ContestCategory::create([
        'name' => 'Category 3',
        'contest_id' => $this->contest->id,
        'auto_assign' => false,
    ]);

    // User belongs to category1 and category2
    $category1->users()->attach($user->id);
    $category2->users()->attach($user->id);

    $response = $this->actingAs($user)->getJson("/api/v1/contests/{$this->contest->id}/user/categories");

    $response->assertStatus(200);
    $response->assertJsonCount(2, 'data');
    $response->assertJsonFragment(['name' => 'Category 1']);
    $response->assertJsonFragment(['name' => 'Category 2']);
});

test('unauthenticated user cannot get their categories', function () {
    $response = $this->getJson("/api/v1/contests/{$this->contest->id}/user/categories");

    $response->assertStatus(401);
});

test('authenticated user can register for non-auto-assign category', function () {
    $user = User::factory()->create();

    $category = ContestCategory::create([
        'name' => 'Test Category',
        'contest_id' => $this->contest->id,
        'auto_assign' => false,
    ]);

    $response = $this->actingAs($user)->postJson("/api/v1/contests/{$this->contest->id}/categories/{$category->id}/register");

    $response->assertStatus(200);
    $response->assertJson(['message' => 'Successfully registered for category']);

    // Verify user is in category
    expect($category->users()->where('user_id', $user->id)->exists())->toBeTrue();
});

test('authenticated user cannot register for auto-assign category', function () {
    $user = User::factory()->create();

    $category = ContestCategory::create([
        'name' => 'Test Category',
        'contest_id' => $this->contest->id,
        'auto_assign' => true,
    ]);

    $response = $this->actingAs($user)->postJson("/api/v1/contests/{$this->contest->id}/categories/{$category->id}/register");

    $response->assertStatus(403);
    $response->assertJson(['error' => 'Cannot manually register for auto-assign categories']);
});

test('unauthenticated user cannot register for category', function () {
    $category = ContestCategory::create([
        'name' => 'Test Category',
        'contest_id' => $this->contest->id,
        'auto_assign' => false,
    ]);

    $response = $this->postJson("/api/v1/contests/{$this->contest->id}/categories/{$category->id}/register");

    $response->assertStatus(401);
});

test('authenticated user can unregister from non-auto-assign category', function () {
    $user = User::factory()->create();

    $category = ContestCategory::create([
        'name' => 'Test Category',
        'contest_id' => $this->contest->id,
        'auto_assign' => false,
    ]);

    // First register the user
    $category->users()->attach($user->id);

    $response = $this->actingAs($user)->deleteJson("/api/v1/contests/{$this->contest->id}/categories/{$category->id}/unregister");

    $response->assertStatus(200);
    $response->assertJson(['message' => 'Successfully unregistered from category']);

    // Verify user is not in category
    expect($category->users()->where('user_id', $user->id)->exists())->toBeFalse();
});

test('authenticated user cannot unregister from auto-assign category', function () {
    $user = User::factory()->create();

    $category = ContestCategory::create([
        'name' => 'Test Category',
        'contest_id' => $this->contest->id,
        'auto_assign' => true,
    ]);

    $response = $this->actingAs($user)->deleteJson("/api/v1/contests/{$this->contest->id}/categories/{$category->id}/unregister");

    $response->assertStatus(403);
    $response->assertJson(['error' => 'Cannot manually unregister from auto-assign categories']);
});

test('unauthenticated user cannot unregister from category', function () {
    $category = ContestCategory::create([
        'name' => 'Test Category',
        'contest_id' => $this->contest->id,
        'auto_assign' => false,
    ]);

    $response = $this->deleteJson("/api/v1/contests/{$this->contest->id}/categories/{$category->id}/unregister");

    $response->assertStatus(401);
});

test('register endpoint returns error if category does not belong to contest', function () {
    $user = User::factory()->create();

    $otherContest = Contest::create([
        'name' => 'Other Contest',
        'description' => 'Other Description',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'free',
        'site_id' => $this->site->id,
    ]);

    $category = ContestCategory::create([
        'name' => 'Test Category',
        'contest_id' => $otherContest->id,
        'auto_assign' => false,
    ]);

    $response = $this->actingAs($user)->postJson("/api/v1/contests/{$this->contest->id}/categories/{$category->id}/register");

    $response->assertStatus(404);
    $response->assertJson(['error' => 'Category does not belong to this contest']);
});

test('unregister endpoint returns error if category does not belong to contest', function () {
    $user = User::factory()->create();

    $otherContest = Contest::create([
        'name' => 'Other Contest',
        'description' => 'Other Description',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'free',
        'site_id' => $this->site->id,
    ]);

    $category = ContestCategory::create([
        'name' => 'Test Category',
        'contest_id' => $otherContest->id,
        'auto_assign' => false,
    ]);

    $response = $this->actingAs($user)->deleteJson("/api/v1/contests/{$this->contest->id}/categories/{$category->id}/unregister");

    $response->assertStatus(404);
    $response->assertJson(['error' => 'Category does not belong to this contest']);
});
