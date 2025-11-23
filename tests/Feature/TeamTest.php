<?php

use App\Models\User;
use App\Models\Site;
use App\Models\Contest;
use App\Models\Team;

beforeEach(function () {
    $this->site = Site::create([
        'name' => 'Test Site',
        'slug' => 'test-site',
        'address' => 'Test Address',
    ]);
});

test('team can be created with max_users', function () {
    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test Description',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'free',
        'site_id' => $this->site->id,
        'team_mode' => 'free',
    ]);

    $user = User::factory()->create();

    $team = Team::create([
        'name' => 'Test Team',
        'contest_id' => $contest->id,
        'max_users' => 5,
        'created_by' => $user->id,
    ]);

    expect($team->name)->toBe('Test Team');
    expect($team->max_users)->toBe(5);
    expect($team->created_by)->toBe($user->id);
    expect($team->contest_id)->toBe($contest->id);
});

test('team can have multiple users', function () {
    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test Description',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'free',
        'site_id' => $this->site->id,
        'team_mode' => 'free',
    ]);

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    $team = Team::create([
        'name' => 'Test Team',
        'contest_id' => $contest->id,
        'max_users' => 5,
        'created_by' => $user1->id,
    ]);

    $team->users()->syncWithoutDetaching([$user1->id, $user2->id, $user3->id]);

    expect($team->users)->toHaveCount(3);
    expect($team->users->pluck('id')->toArray())->toContain($user1->id, $user2->id, $user3->id);
});

test('team isFull returns correct value', function () {
    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test Description',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'free',
        'site_id' => $this->site->id,
        'team_mode' => 'free',
    ]);

    $team = Team::create([
        'name' => 'Test Team',
        'contest_id' => $contest->id,
        'max_users' => 2,
        'created_by' => User::factory()->create()->id,
    ]);

    expect($team->isFull())->toBeFalse();

    $team->users()->syncWithoutDetaching([User::factory()->create()->id, User::factory()->create()->id]);
    $team->refresh();

    expect($team->isFull())->toBeTrue();
});

test('team can generate invitation token', function () {
    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test Description',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'free',
        'site_id' => $this->site->id,
        'team_mode' => 'free',
    ]);

    $team = Team::create([
        'name' => 'Test Team',
        'contest_id' => $contest->id,
        'max_users' => 5,
        'created_by' => User::factory()->create()->id,
    ]);

    expect($team->invitation_token)->toBeNull();

    $token = $team->generateInvitationToken();

    expect($token)->not->toBeNull();
    expect($token)->toHaveLength(64);
    expect($team->invitation_token)->toBe($token);
});

test('contest team_mode can be set to different values', function () {
    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test Description',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'free',
        'site_id' => $this->site->id,
        'team_mode' => 'free',
    ]);

    expect($contest->team_mode)->toBe('free');
    expect($contest->isTeamModeFree())->toBeTrue();
    expect($contest->isTeamModeRegister())->toBeFalse();
    expect($contest->isTeamModeRestricted())->toBeFalse();

    $contest->update(['team_mode' => 'register']);
    $contest->refresh();

    expect($contest->team_mode)->toBe('register');
    expect($contest->isTeamModeFree())->toBeFalse();
    expect($contest->isTeamModeRegister())->toBeTrue();
    expect($contest->isTeamModeRestricted())->toBeFalse();

    $contest->update(['team_mode' => 'restricted']);
    $contest->refresh();

    expect($contest->team_mode)->toBe('restricted');
    expect($contest->isTeamModeFree())->toBeFalse();
    expect($contest->isTeamModeRegister())->toBeFalse();
    expect($contest->isTeamModeRestricted())->toBeTrue();

    $contest->update(['team_mode' => null]);
    $contest->refresh();

    expect($contest->team_mode)->toBeNull();
    expect($contest->hasTeamMode())->toBeFalse();
});

test('user can only be in one team per contest', function () {
    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test Description',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'free',
        'site_id' => $this->site->id,
        'team_mode' => 'free',
    ]);

    $user = User::factory()->create();

    $team1 = Team::create([
        'name' => 'Team 1',
        'contest_id' => $contest->id,
        'max_users' => 5,
    ]);

    $team2 = Team::create([
        'name' => 'Team 2',
        'contest_id' => $contest->id,
        'max_users' => 5,
    ]);

    $team1->users()->syncWithoutDetaching([$user->id]);

    // User should be in team1
    expect($contest->teams()->whereHas('users', function ($query) use ($user) {
        $query->where('user_id', $user->id);
    })->count())->toBe(1);

    expect($team1->users->contains($user->id))->toBeTrue();
    expect($team2->users->contains($user->id))->toBeFalse();
});

test('team belongs to contest and creator', function () {
    $contest = Contest::create([
        'name' => 'Test Contest',
        'description' => 'Test Description',
        'start_date' => now(),
        'end_date' => now()->addDays(7),
        'mode' => 'free',
        'site_id' => $this->site->id,
        'team_mode' => 'free',
    ]);

    $creator = User::factory()->create();

    $team = Team::create([
        'name' => 'Test Team',
        'contest_id' => $contest->id,
        'max_users' => 5,
        'created_by' => $creator->id,
    ]);

    expect($team->contest)->toBeInstanceOf(Contest::class);
    expect($team->contest->id)->toBe($contest->id);
    expect($team->creator)->toBeInstanceOf(User::class);
    expect($team->creator->id)->toBe($creator->id);
});
