<?php

use App\Models\Contest;
use App\Models\Site;
use App\Models\User;
use Spatie\Permission\Models\Permission;

test('official contest creates permission on creation', function () {
    $site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);

    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'official',
        'site_id' => $site->id,
    ]);

    $permissionName = 'contest.' . $contest->id;
    $permission = Permission::where('name', $permissionName)->first();
    
    expect($permission)->not->toBeNull();
    expect($permission->name)->toBe($permissionName);
});

test('free contest does not create permission on creation', function () {
    $site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);

    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'free',
        'site_id' => $site->id,
    ]);

    $permissionName = 'contest.' . $contest->id;
    $permission = Permission::where('name', $permissionName)->first();
    
    expect($permission)->toBeNull();
});

test('adding staff member gives permission', function () {
    $site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);

    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'official',
        'site_id' => $site->id,
    ]);

    $user = User::factory()->create();
    
    expect($contest->isStaffMember($user))->toBeFalse();
    
    $contest->addStaffMember($user);
    
    expect($contest->isStaffMember($user))->toBeTrue();
    expect($user->hasPermissionTo('contest.' . $contest->id))->toBeTrue();
});

test('removing staff member revokes permission', function () {
    $site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);

    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'official',
        'site_id' => $site->id,
    ]);

    $user = User::factory()->create();
    $contest->addStaffMember($user);
    
    expect($contest->isStaffMember($user))->toBeTrue();
    
    $contest->removeStaffMember($user);
    
    expect($contest->isStaffMember($user))->toBeFalse();
    expect($user->hasPermissionTo('contest.' . $contest->id))->toBeFalse();
});

test('changing contest mode from official to free deletes permission', function () {
    $site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);

    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'official',
        'site_id' => $site->id,
    ]);

    $permissionName = 'contest.' . $contest->id;
    expect(Permission::where('name', $permissionName)->first())->not->toBeNull();
    
    $contest->update(['mode' => 'free']);
    
    expect(Permission::where('name', $permissionName)->first())->toBeNull();
});

test('deleting contest deletes permission', function () {
    $site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);

    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'official',
        'site_id' => $site->id,
    ]);

    $permissionName = 'contest.' . $contest->id;
    expect(Permission::where('name', $permissionName)->first())->not->toBeNull();
    
    $contest->delete();
    
    expect(Permission::where('name', $permissionName)->first())->toBeNull();
});

test('staff members have access permission to contest registrations', function () {
    $site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);

    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'official',
        'site_id' => $site->id,
    ]);

    $user = User::factory()->create();
    $contest->addStaffMember($user);
    
    expect($user->can('access_registrations', $contest))->toBeTrue();
});

test('non-staff members do not have access permission to contest registrations', function () {
    $site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);

    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'official',
        'site_id' => $site->id,
    ]);

    $user = User::factory()->create();
    
    expect($user->can('access_registrations', $contest))->toBeFalse();
});
