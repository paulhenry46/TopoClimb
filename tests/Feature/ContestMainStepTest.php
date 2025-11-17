<?php

use App\Models\Contest;
use App\Models\Site;
use App\Models\ContestStep;

test('contest automatically creates main step on creation', function () {
    $site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);

    $startDate = now();
    $endDate = now()->addDays(7);

    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test Description',
        'start_date' => $startDate,
        'end_date' => $endDate,
        'mode' => 'free',
        'site_id' => $site->id,
    ]);

    // Verify main step was created
    expect($contest->steps)->toHaveCount(1);
    
    $mainStep = $contest->steps->first();
    expect($mainStep->name)->toBe('Main');
    expect($mainStep->order)->toBe(0);
    expect($mainStep->start_time->timestamp)->toBe($startDate->timestamp);
    expect($mainStep->end_time->timestamp)->toBe($endDate->timestamp);
});

test('contest main step dates update with contest dates', function () {
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

    $mainStep = $contest->mainStep();
    expect($mainStep)->not->toBeNull();
    
    $oldStartTime = $mainStep->start_time;
    $oldEndTime = $mainStep->end_time;

    // Update contest dates
    $newStartDate = now()->addDay();
    $newEndDate = now()->addDays(10);
    
    $contest->update([
        'start_date' => $newStartDate,
        'end_date' => $newEndDate,
    ]);

    // Reload main step
    $mainStep->refresh();
    
    expect($mainStep->start_time->timestamp)->toBe($newStartDate->timestamp);
    expect($mainStep->end_time->timestamp)->toBe($newEndDate->timestamp);
    expect($mainStep->start_time->timestamp)->not->toBe($oldStartTime->timestamp);
    expect($mainStep->end_time->timestamp)->not->toBe($oldEndTime->timestamp);
});

test('contest routes are managed through main step', function () {
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

    // Attach route to contest
    $contest->routes()->attach($route->id, ['points' => 150]);

    // Verify route is accessible through contest
    expect($contest->routes()->count())->toBe(1);
    expect($contest->routes->first()->id)->toBe($route->id);
    expect($contest->routes->first()->pivot->points)->toBe(150);

    // Verify route is in main step
    $mainStep = $contest->mainStep();
    expect($mainStep->routes()->count())->toBe(1);
    expect($mainStep->routes->first()->id)->toBe($route->id);
    expect($mainStep->routes->first()->pivot->points)->toBe(150);
});

test('main step method returns correct step', function () {
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

    $mainStep = $contest->mainStep();
    
    expect($mainStep)->toBeInstanceOf(ContestStep::class);
    expect($mainStep->name)->toBe('Main');
    expect($mainStep->order)->toBe(0);
    expect($mainStep->contest_id)->toBe($contest->id);
});
