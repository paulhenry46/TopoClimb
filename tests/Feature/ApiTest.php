<?php

use App\Models\User;
use App\Models\Site;
use App\Models\Area;
use App\Models\Sector;
use App\Models\Line;
use App\Models\Route;
use App\Models\Contest;
use App\Models\Team;
use App\Models\Tag;

test('can list all sites', function () {
    // Create test sites
    Site::factory()->count(3)->create();
    
    $response = $this->getJson('/api/v1/sites');
    
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'name',
                'slug',
            ]
        ]
    ]);
});

test('can get a single site', function () {
    $site = Site::factory()->create();
    
    $response = $this->getJson("/api/v1/sites/{$site->id}");
    
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id',
            'name',
            'slug',
        ]
    ]);
});

test('site includes grading system in API response', function () {
    $site = Site::factory()->create([
        'default_cotation' => true,
    ]);
    
    $response = $this->getJson("/api/v1/sites/{$site->id}");
    
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id',
            'name',
            'slug',
            'default_cotation',
            'grading_system' => [
                'free',
                'hint',
                'points',
            ],
        ]
    ]);
    
    // Verify it returns default grading system
    $response->assertJsonPath('data.default_cotation', true);
    $response->assertJsonPath('data.grading_system.free', false);
    expect($response->json('data.grading_system.points'))->toBeArray();
});

test('site with custom grading system returns custom grades', function () {
    $customGradingSystem = [
        'free' => false,
        'hint' => 'Custom grading: Easy, Medium, Hard',
        'points' => [
            'Easy' => 300,
            'Medium' => 600,
            'Hard' => 900,
        ],
    ];
    
    $site = Site::factory()->create([
        'default_cotation' => false,
        'custom_cotation' => $customGradingSystem,
    ]);
    
    $response = $this->getJson("/api/v1/sites/{$site->id}");
    
    $response->assertStatus(200);
    $response->assertJsonPath('data.default_cotation', false);
    $response->assertJsonPath('data.grading_system.free', false);
    $response->assertJsonPath('data.grading_system.hint', 'Custom grading: Easy, Medium, Hard');
    $response->assertJsonPath('data.grading_system.points.Easy', 300);
    $response->assertJsonPath('data.grading_system.points.Medium', 600);
    $response->assertJsonPath('data.grading_system.points.Hard', 900);
});

test('can list areas for a site', function () {
    $site = Site::factory()->create();
    Area::factory()->count(2)->create(['site_id' => $site->id]);
    
    $response = $this->getJson("/api/v1/sites/{$site->id}/areas");
    
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'name',
                'slug',
            ]
        ]
    ]);
});

test('can list routes for an area', function () {
    $site = Site::factory()->create();
    $area = Area::factory()->create(['site_id' => $site->id]);
    $sector = Sector::factory()->create(['area_id' => $area->id]);
    $line = Line::factory()->create(['sector_id' => $sector->id]);
    Route::factory()->count(3)->create(['line_id' => $line->id]);
    
    $response = $this->getJson("/api/v1/areas/{$area->id}/routes");
    
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'name',
                'slug',
            ]
        ]
    ]);
    $response->assertJsonCount(3, 'data');
});

test('can get authenticated user profile', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;
    
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/api/v1/user');
    
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            'id',
            'name',
            'email',
        ]
    ]);
});

test('can update authenticated user profile', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test-token')->plainTextToken;
    
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->putJson('/api/v1/user', [
            'name' => 'Updated Name',
        ]);
    
    $response->assertStatus(200);
    $response->assertJsonPath('data.name', 'Updated Name');
});

test('cannot access user profile without authentication', function () {
    $response = $this->getJson('/api/v1/user');
    
    $response->assertStatus(401);
});

test('can list tags', function () {
    Tag::factory()->count(3)->create();
    
    $response = $this->getJson('/api/v1/tags');
    
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'name',
            ]
        ]
    ]);
});
