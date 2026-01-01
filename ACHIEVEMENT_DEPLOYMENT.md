# Achievement System - Deployment Guide

## Installation Steps

### 1. Run Migrations

After merging this PR, run the migrations to create the achievements tables:

```bash
php artisan migrate
```

This will create:
- `achievements` table
- `user_achievements` table

### 2. Seed Initial Achievements

Populate the database with the default achievement definitions:

```bash
php artisan db:seed --class=AchievementSeeder
```

This will create:
- 9 max grade achievements (5a through 8a)
- 6 total routes achievements (10, 25, 50, 100, 250, 500 routes)
- 5 grade count achievements (various combinations)

### 3. Verify Installation

Run the tests to ensure everything is working correctly:

```bash
php artisan test --filter=AchievementTest
```

All 6 tests should pass.

## Features Overview

### Automatic Achievement Unlocking

The system automatically evaluates and unlocks achievements whenever a user creates a climbing log. No additional code is needed - it's handled by the `LogObserver`.

### Available Achievement Types

1. **Max Grade**: User climbs a route at or above a specific grade
2. **Total Routes**: User climbs N total distinct routes
3. **Grade Count**: User climbs N routes at or above a specific grade
4. **Contest**: User participates in a specific contest (time-limited)

### Adding Contest Achievements

To create a contest-specific achievement:

```php
use App\Services\AchievementService;

$service = new AchievementService();
$achievement = $service->createContestAchievement(
    $contest->id,
    "Participant - {$contest->name}",
    "Participated in {$contest->name}"
);
```

## Database Schema

### achievements
- id
- key (unique)
- name
- description
- type
- criteria (JSON)
- contest_id (nullable)
- created_at, updated_at

### user_achievements
- id
- user_id
- achievement_id
- unlocked_at
- created_at, updated_at
- UNIQUE(user_id, achievement_id)

## Usage Examples

### Check if User Has Achievement

```php
if ($user->hasAchievement('max_grade_700')) {
    // User has climbed 7a or harder
}
```

### Get All User Achievements

```php
$achievements = $user->achievements;

foreach ($achievements as $achievement) {
    echo $achievement->name;
    echo $achievement->pivot->unlocked_at; // When it was unlocked
}
```

### Manually Trigger Evaluation (if needed)

```php
use App\Services\AchievementService;

$service = new AchievementService();
$newAchievements = $service->evaluateAchievements($user);
```

## Performance Considerations

- Achievements are evaluated once per log creation via observer
- Database queries use indexed columns (user_id, achievement_id)
- The `hasAchievement` check prevents duplicate evaluations
- Race conditions are handled with `firstOrCreate`

## Future Enhancements

See ACHIEVEMENT_SYSTEM.md for ideas on future enhancements like:
- Regularity achievements (weekly/monthly climbing)
- Social achievements (climb with friends)
- Variety achievements (different sites, colors)
- Achievement tiers (Bronze, Silver, Gold)
- Time-limited seasonal challenges

## Troubleshooting

### Achievements not unlocking

1. Check that migrations were run: `php artisan migrate:status`
2. Verify achievements exist in database: `php artisan tinker` then `Achievement::count()`
3. Check if LogObserver is registered in EventServiceProvider
4. Verify logs are being created successfully

### Duplicate achievement errors

The system handles this gracefully with `firstOrCreate`, but if you see errors:
1. Check the unique constraint exists on user_achievements table
2. Verify the database supports the constraint

## Support

For detailed documentation, see ACHIEVEMENT_SYSTEM.md
For technical questions, contact the development team
