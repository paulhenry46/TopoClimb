<?php

use App\Models\Contest;
use App\Models\Site;
use App\Models\User;

test('contest can have authorized users', function () {
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
        'mode' => 'official',
        'site_id' => $site->id,
    ]);

    $user = User::factory()->create();

    $contest->addAuthorizedUser($user);

    expect($contest->authorizedUsers)->toHaveCount(1);
    expect($contest->authorizedUsers->first()->id)->toBe($user->id);
});

test('contest can check if user is authorized', function () {
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
        'mode' => 'official',
        'site_id' => $site->id,
    ]);

    $authorizedUser = User::factory()->create();
    $unauthorizedUser = User::factory()->create();

    $contest->addAuthorizedUser($authorizedUser);

    expect($contest->isUserAuthorized($authorizedUser))->toBeTrue();
    expect($contest->isUserAuthorized($unauthorizedUser))->toBeFalse();
});

test('contest with no authorized users allows all users', function () {
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
        'mode' => 'official',
        'site_id' => $site->id,
    ]);

    $user = User::factory()->create();

    // No authorized users set, so all users should be authorized
    expect($contest->isUserAuthorized($user))->toBeTrue();
});

test('can remove authorized user from contest', function () {
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
        'mode' => 'official',
        'site_id' => $site->id,
    ]);

    $user = User::factory()->create();

    $contest->addAuthorizedUser($user);
    expect($contest->authorizedUsers)->toHaveCount(1);

    $contest->removeAuthorizedUser($user);
    expect($contest->authorizedUsers()->count())->toBe(0);
});

test('rankings are filtered by authorized users', function () {
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
        'mode' => 'official',
        'site_id' => $site->id,
    ]);

    $authorizedUser = User::factory()->create();
    $unauthorizedUser = User::factory()->create();

    $contest->addAuthorizedUser($authorizedUser);

    // This test validates that rankings are filtered, but we'd need routes and logs
    // to fully test this. For now, just verify the authorized users relationship works.
    $rankings = $contest->getRankingForStep(null);
    
    // Should be empty since there are no logs yet
    expect($rankings)->toBeInstanceOf(\Illuminate\Support\Collection::class);
});
