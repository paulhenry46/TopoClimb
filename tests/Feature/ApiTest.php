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
