<?php

use App\Models\User;
use App\Models\Site;
use App\Models\Contest;
use App\Models\Route;
use App\Models\ContestRegistration;

test('contest can be created', function () {
    $user = User::factory()->create();
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

    expect($contest->name)->toBe('Test Contest');
    expect($contest->mode)->toBe('free');
    expect($contest->site_id)->toBe($site->id);
});

test('contest belongs to site', function () {
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

    expect($contest->site)->toBeInstanceOf(Site::class);
    expect($contest->site->id)->toBe($site->id);
});

test('contest can have routes', function () {
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

    $contest->routes()->attach($route->id);

    expect($contest->routes)->toHaveCount(1);
    expect($contest->routes->first()->id)->toBe($route->id);
});

test('contest status methods work correctly', function () {
    $site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);

    // Active contest
    $activeContest = Contest::create([
        'name' => 'Active Contest',
        'description' => 'Test Description',
        'start_date' => now()->subDay(),
        'end_date' => now()->addDay(),
        'mode' => 'free',
        'site_id' => $site->id,
    ]);

    expect($activeContest->isActive())->toBeTrue();
    expect($activeContest->isPast())->toBeFalse();
    expect($activeContest->isFuture())->toBeFalse();

    // Future contest
    $futureContest = Contest::create([
        'name' => 'Future Contest',
        'description' => 'Test Description',
        'start_date' => now()->addDay(),
        'end_date' => now()->addDays(7),
        'mode' => 'free',
        'site_id' => $site->id,
    ]);

    expect($futureContest->isActive())->toBeFalse();
    expect($futureContest->isPast())->toBeFalse();
    expect($futureContest->isFuture())->toBeTrue();

    // Past contest
    $pastContest = Contest::create([
        'name' => 'Past Contest',
        'description' => 'Test Description',
        'start_date' => now()->subDays(7),
        'end_date' => now()->subDay(),
        'mode' => 'free',
        'site_id' => $site->id,
    ]);

    expect($pastContest->isActive())->toBeFalse();
    expect($pastContest->isPast())->toBeTrue();
    expect($pastContest->isFuture())->toBeFalse();
});

test('contest registration can be created', function () {
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

    $user = User::factory()->create();
    $registrar = User::factory()->create();

    $registration = ContestRegistration::create([
        'contest_id' => $contest->id,
        'route_id' => $route->id,
        'user_id' => $user->id,
        'registered_by' => $registrar->id,
    ]);

    expect($registration->contest_id)->toBe($contest->id);
    expect($registration->route_id)->toBe($route->id);
    expect($registration->user_id)->toBe($user->id);
    expect($registration->registered_by)->toBe($registrar->id);
});
