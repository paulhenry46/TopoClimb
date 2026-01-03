# Advanced Climber Statistics Feature

## Overview
This feature adds comprehensive statistics tracking and a new "tentative" (attempt) log type to help climbers track their progress more accurately.

## New Features

### 1. Tentative Logs (Private Attempts)
- **New log type**: "tentative" for tracking attempts on routes
- **Private by default**: Only visible to the user who created them
- **No limits**: Multiple attempts can be logged for the same route
- **Usage**: Track your progress on projects without making your attempts public

### 2. User Statistics Table
A new `user_stats` table stores calculated statistics with a one-to-one relationship to users. This table includes:

#### Technical Analysis
- **Consistency variance**: Measures stability in climbing level
- **Flash/Work ratio**: Shows explosive vs methodical climbing style
- **Risk profile**: Abandonment rate on challenging routes
- **Endurance vs Power**: Long routes vs short boulder problems
- **Movement preferences**: Based on route tags (coordination, balance, resistance, etc.)

#### Behavioral Analysis
- **Climbing habits**: Preferred climbing hours, session duration
- **Exploration ratio**: New routes vs repeated routes
- **Sector fidelity**: Most frequented climbing sectors
- **Patience/Tenacity**: Average attempts before success, project count

#### Progression Analysis
- **Progression rate**: Level increase per month
- **Plateau detection**: Identifies stagnation periods
- **Progression by style**: Tracking improvement in slab, overhang, vertical
- **Progression by sector**: Which sectors show fastest improvement

#### Training Load Analysis
- **Weekly volume and intensity**: Total climbing load
- **Acute/Chronic load ratio**: Helps detect overtraining (>1.5 ratio)
- **Recovery metrics**: Time between sessions and performances

### 3. Nightly Statistics Calculation
- Statistics are automatically updated every night at 2 AM
- Can be manually triggered with: `php artisan stats:calculate`
- Calculate for specific user: `php artisan stats:calculate --user_id=123`

## Database Changes

### Logs Table
- Added `type` enum value: `'tentative'`
- Added `is_public` boolean field (default: true)
- Tentative logs automatically set `is_public = false`

### User Stats Table
New table with comprehensive metrics for each user. See migration for full schema.

## Privacy & Security

### Tentative Logs
- ✅ Only visible to the user who created them
- ✅ Not counted in public profile statistics
- ✅ Not included in contest calculations
- ✅ Don't trigger achievement unlocks
- ✅ Multiple attempts allowed per route

### Public Logs (work, flash, view)
- ✅ Visible in public profiles
- ✅ Counted in contests
- ✅ Trigger achievement unlocks
- ✅ Limited to one per route+way combination

## API Changes

### UserController
- `publicProfile()`: Now filters out tentative logs
- `stats()`: Includes all user's logs (for authenticated user)

### Contest Calculations
All contest and team point calculations now filter out tentative logs to ensure fair competition.

## UI Changes

### Logger Component
New option added: **"Attempt (Private)"**
- Icon: Gray refresh icon
- Description: "Log an attempt on the route. This log will be private and only visible to you for tracking your progress."
- Automatically sets `is_public = false`

## Usage Examples

### Tracking a Project
1. Attempt a hard route → Log as "Attempt (Private)"
2. Try again next session → Log another "Attempt (Private)"
3. Finally send it → Log as "After work" (public)

The stats system will:
- Count your attempts in "avg_attempts_before_success"
- Include the route in "project_count" if worked over multiple sessions
- Track time between attempts
- Not show attempts publicly until you log the send

### Viewing Statistics
Statistics are automatically calculated nightly. Access them via:
- API: `/api/user/stats` (planned endpoint)
- Database: Query `user_stats` table
- Command line: View in database after running `php artisan stats:calculate`

## Testing

### Run Tests
```bash
php artisan test --filter=LogPrivacy
php artisan test --filter=StatsCalculation
```

### Manual Testing
1. Create tentative logs via the logger UI
2. Verify they don't appear in public profile
3. Run stats calculation: `php artisan stats:calculate --user_id=YOUR_ID`
4. Check the `user_stats` table for calculated metrics

## Migration

### Running Migrations
```bash
php artisan migrate
```

This will:
1. Add `is_public` column to logs table
2. Update `type` enum to include 'tentative'
3. Create `user_stats` table

### Existing Data
- All existing logs will have `is_public = true` by default
- Run `php artisan stats:calculate` to populate statistics for existing users

## Notes

- Statistics use both public and tentative logs for calculations (where appropriate)
- Tentative logs help calculate metrics like "attempts before success" and "abandonment rate"
- The scheduler must be running for nightly updates: `php artisan schedule:work`
- Consider setting up a cron job for production: `* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1`
