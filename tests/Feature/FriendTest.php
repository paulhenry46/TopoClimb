<?php

use App\Models\User;

test('can get friends list', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->getJson('/api/v1/user/friends');
    
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [],
    ]);
});

test('can search users by name', function () {
    $user = User::factory()->create();
    $searchUser = User::factory()->create(['name' => 'John Doe']);
    
    $response = $this->actingAs($user)->getJson('/api/v1/users/search?query=John');
    
    $response->assertStatus(200);
    $response->assertJsonStructure([
        'data' => [
            '*' => [
                'id',
                'name',
                'profile_photo_url',
            ],
        ],
    ]);
});

test('can add a friend', function () {
    $user = User::factory()->create();
    $friend = User::factory()->create();
    
    $response = $this->actingAs($user)->postJson('/api/v1/user/friends', [
        'friend_id' => $friend->id,
    ]);
    
    $response->assertStatus(201);
    
    // Verify the friendship exists
    expect($user->friends->contains($friend->id))->toBeTrue();
});

test('cannot add yourself as friend', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)->postJson('/api/v1/user/friends', [
        'friend_id' => $user->id,
    ]);
    
    $response->assertStatus(400);
});

test('cannot add the same friend twice', function () {
    $user = User::factory()->create();
    $friend = User::factory()->create();
    
    // Add friend first time
    $this->actingAs($user)->postJson('/api/v1/user/friends', [
        'friend_id' => $friend->id,
    ]);
    
    // Try to add again
    $response = $this->actingAs($user)->postJson('/api/v1/user/friends', [
        'friend_id' => $friend->id,
    ]);
    
    $response->assertStatus(400);
});

test('can remove a friend', function () {
    $user = User::factory()->create();
    $friend = User::factory()->create();
    
    // Add friend first
    $user->friends()->attach($friend->id);
    
    $response = $this->actingAs($user)->deleteJson("/api/v1/user/friends/{$friend->id}");
    
    $response->assertStatus(200);
    
    // Verify the friendship is removed
    expect($user->friends->contains($friend->id))->toBeFalse();
});

test('friends list includes bidirectional friendships', function () {
    $user = User::factory()->create();
    $friend1 = User::factory()->create();
    $friend2 = User::factory()->create();
    
    // User adds friend1
    $user->friends()->attach($friend1->id);
    
    // Friend2 adds user
    $friend2->friends()->attach($user->id);
    
    $response = $this->actingAs($user)->getJson('/api/v1/user/friends');
    
    $response->assertStatus(200);
    $data = $response->json('data');
    
    expect(count($data))->toBe(2);
});
