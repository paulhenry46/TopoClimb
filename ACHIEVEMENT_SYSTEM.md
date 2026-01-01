# Achievement System Documentation

## Overview

The achievement system (système de réussites) allows TopoClimb to automatically award badges to users based on their climbing activity. The system is flexible, extensible, and easy to maintain.

## Architecture

### Database Schema

#### `achievements` table
Stores all available achievements that can be unlocked:
- `id`: Primary key
- `key`: Unique identifier (e.g., 'max_grade_600')
- `name`: Display name (e.g., 'Grimpeur 6a')
- `description`: Description of how to unlock
- `type`: Achievement type ('max_grade', 'total_routes', 'grade_count', 'contest')
- `criteria`: JSON storing evaluation criteria
- `contest_id`: Optional foreign key for contest-specific achievements
- `created_at`, `updated_at`: Timestamps

#### `user_achievements` table
Tracks which achievements users have unlocked:
- `id`: Primary key
- `user_id`: Foreign key to users
- `achievement_id`: Foreign key to achievements
- `unlocked_at`: When the achievement was unlocked
- `created_at`, `updated_at`: Timestamps
- Unique constraint on (user_id, achievement_id) to prevent duplicates

### Models

#### `Achievement` Model
Represents an achievement definition.

**Relationships:**
- `contest()`: BelongsTo Contest (optional)
- `users()`: BelongsToMany User through user_achievements

#### `UserAchievement` Model
Pivot model tracking unlocked achievements.

**Relationships:**
- `user()`: BelongsTo User
- `achievement()`: BelongsTo Achievement

#### `User` Model Extensions
New methods added:
- `achievements()`: Get all unlocked achievements
- `hasAchievement($key)`: Check if user has unlocked a specific achievement

## Achievement Types

### 1. Max Grade Achievement
Awards user for reaching a specific climbing grade.

**Example:**
```php
new MaxGradeAchievement(600, '6a')
```

**Criteria:**
- Requires at least one log with grade >= required grade

### 2. Total Routes Achievement
Awards user for climbing a total number of distinct routes.

**Example:**
```php
new TotalRoutesAchievement(50)
```

**Criteria:**
- Requires distinct route count >= required count

### 3. Grade Count Achievement
Awards user for climbing a specific number of routes at or above a grade.

**Example:**
```php
new GradeCountAchievement(610, 10, '6a+')
```

**Criteria:**
- Requires count of distinct routes with grade >= min_grade to be >= required_count

### 4. Contest Achievement
Awards user for participating in a specific contest.

**Example:**
```php
new ContestAchievement($contestId, 'Contest Champion', 'Participated in contest')
```

**Criteria:**
- Requires at least one log for a route in the contest

## Achievement Service

The `AchievementService` class manages achievement definitions and evaluation.

### Key Methods

#### `getAllAchievementDefinitions(): array`
Returns all predefined achievement definitions.

Currently includes:
- Max grade achievements: 5a, 5c, 6a, 6a+, 6b, 6c, 7a, 7b, 8a
- Total routes: 10, 25, 50, 100, 250, 500
- Grade count: 10x 6a+, 10x 6b, 10x 6c, 5x 7a, 10x 7a

#### `syncAchievements(): void`
Synchronizes achievement definitions to the database.
Should be called when:
- Initial setup (via seeder)
- After adding new achievement definitions
- During deployment

#### `evaluateAchievements(User $user): array`
Evaluates all achievements for a user and awards newly unlocked ones.

**Returns:** Array of newly unlocked achievement keys

**Usage:**
```php
$service = new AchievementService();
$newAchievements = $service->evaluateAchievements($user);
```

#### `createContestAchievement(int $contestId, string $name, string $description): Achievement`
Creates a contest-specific achievement.

**Usage:**
```php
$service = new AchievementService();
$achievement = $service->createContestAchievement(
    $contest->id,
    'Winter Contest 2026',
    'Participated in the Winter Contest 2026'
);
```

#### `evaluateContestAchievement(User $user, int $contestId): bool`
Evaluates and awards contest achievement for a user.

**Returns:** True if newly unlocked, false otherwise

## Adding New Achievement Types

To add a new achievement type:

1. Create a new class extending `BaseAchievement`:

```php
<?php

namespace App\Achievements;

use App\Models\User;

class MyCustomAchievement extends BaseAchievement
{
    public function getKey(): string
    {
        return 'my_custom_achievement';
    }

    public function getName(): string
    {
        return 'My Custom Achievement';
    }

    public function getDescription(): string
    {
        return 'Description of how to unlock';
    }

    public function getType(): string
    {
        return 'custom';
    }

    public function getCriteria(): array
    {
        return [
            'custom_criteria' => 'value',
        ];
    }

    public function isUnlocked(User $user): bool
    {
        // Implement evaluation logic
        return true;
    }
}
```

2. Add the achievement to `AchievementService::getAllAchievementDefinitions()`:

```php
public function getAllAchievementDefinitions(): array
{
    $achievements = [];
    
    // ... existing achievements ...
    
    // Add your custom achievement
    $achievements[] = new MyCustomAchievement();
    
    return $achievements;
}
```

3. Run the seeder to sync:

```bash
php artisan db:seed --class=AchievementSeeder
```

## Usage Examples

### Automatic Evaluation (Recommended)

Achievements are automatically evaluated whenever a user creates a climbing log. This is handled by the `LogObserver` which calls the `AchievementService` after each log is created.

No additional code is required - achievements will be automatically unlocked as users climb!

### Initial Setup

Run the achievement seeder to populate the database:

```bash
php artisan db:seed --class=AchievementSeeder
```

### Manual Evaluation (Optional)

If you need to manually trigger achievement evaluation (for example, to re-evaluate all users after adding new achievements), you can call the service directly:

```php
use App\Services\AchievementService;

// Evaluate for a specific user
$service = new AchievementService();
$newAchievements = $service->evaluateAchievements($user);

// If new achievements were unlocked
if (!empty($newAchievements)) {
    foreach ($newAchievements as $key) {
        $achievement = Achievement::where('key', $key)->first();
        // Display notification or message
    }
}
```

Note: In normal operation, automatic evaluation via the LogObserver is sufficient.

### Checking User's Achievements

```php
// Get all achievements for a user
$achievements = $user->achievements;

// Check if user has a specific achievement
if ($user->hasAchievement('max_grade_700')) {
    // User has climbed 7a or harder
}

// Get achievement with unlock date
$userAchievement = $user->achievements()
    ->where('key', 'total_routes_100')
    ->first();
    
if ($userAchievement) {
    $unlockedAt = $userAchievement->pivot->unlocked_at;
}
```

### Creating Contest Achievements

When creating a contest, optionally create an achievement:

```php
use App\Services\AchievementService;

$contest = Contest::create([...]);

$service = new AchievementService();
$achievement = $service->createContestAchievement(
    $contest->id,
    "Participant - {$contest->name}",
    "Participated in {$contest->name}"
);
```

## Testing

The achievement system includes comprehensive tests in `tests/Feature/AchievementTest.php`.

Run tests:
```bash
php artisan test --filter=AchievementTest
```

Tests cover:
- Achievement synchronization
- Max grade achievement unlocking
- Total routes achievement unlocking
- Grade count achievement unlocking
- Duplicate prevention
- Multiple achievement unlocking

## Future Enhancements

Potential additions to the system:

1. **Regularity Achievements**
   - Weekly/monthly climbing frequency
   - Consecutive weeks/months climbing

2. **Social Achievements**
   - Climb with X different friends
   - Verify X climbs for others

3. **Variety Achievements**
   - Climb routes of all colors
   - Climb in X different sites

4. **Achievement Tiers**
   - Bronze, Silver, Gold levels for same achievement
   - Progressive milestones

5. **Time-Limited Achievements**
   - Seasonal challenges
   - Monthly goals

6. **Display Features**
   - Achievement badges/icons
   - User profile achievement showcase
   - Leaderboards for achievements

## Notes

- Achievements are evaluated on-demand, not automatically on every log creation
- The system is designed to be performant with database queries using indices
- Contest achievements are only created when explicitly requested
- Achievement definitions should be immutable once created (don't change criteria for existing achievements)
- To modify an achievement, create a new one with a different key
