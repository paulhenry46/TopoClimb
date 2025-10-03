# Contest Features Implementation Summary

## Overview
This implementation adds three major features to the contest system:

1. **Points Per Route**: Each route in a contest can now have a customizable number of points
2. **Dynamic Points Calculation**: Points are automatically divided by the number of climbers who have completed the route
3. **Multi-Step Contests**: Support for qualification waves and contest steps with specific time periods

## Database Changes

### Migration: `add_points_to_contest_route_table`
- Adds `points` column (integer, default: 100) to the `contest_route` pivot table
- This allows each route in a contest to have a specific point value

### Migration: `create_contest_steps_table`
Creates a new table for contest steps/waves with the following columns:
- `id`: Primary key
- `contest_id`: Foreign key to contests table
- `name`: Name of the step (e.g., "Pre-qualification Wave 1", "Final")
- `order`: Integer for sorting steps
- `start_time`: DateTime when the step starts
- `end_time`: DateTime when the step ends
- `timestamps`: Created at and updated at

## Model Changes

### Contest Model (`app/Models/Contest.php`)
1. **Updated `routes()` relationship**: Now includes `withPivot('points')` and `withTimestamps()`
2. **Added `steps()` relationship**: Returns contest steps ordered by their order field
3. **Added `getRoutePoints($routeId)` method**: 
   - Returns the dynamic points for a route
   - Calculates points by dividing base points by the number of unique climbers who completed it
   - Only counts verified logs within the contest date range
   - Returns base points if no climbers have completed the route

### ContestStep Model (`app/Models/ContestStep.php`)
New model with the following features:
- Fillable fields: `contest_id`, `name`, `order`, `start_time`, `end_time`
- DateTime casts for `start_time` and `end_time`
- Relationship: `contest()` - belongs to Contest
- Status methods: `isActive()`, `isPast()`, `isFuture()`

## UI Changes

### Routes Selector (`resources/views/livewire/contests/routes-selector.blade.php`)
Enhanced to support points management:
- Added `routePoints` property to track points for each selected route
- Updated `toggleRoute()` to set default 100 points when adding a route
- Added `updatePoints()` method to save point changes
- UI now shows an input field for points next to each selected route
- Points are updated in real-time when changed

### Contest Steps Manager (`resources/views/livewire/contests/steps-manager.blade.php`)
New Livewire component for managing contest steps:
- Form to add/edit contest steps with fields for:
  - Step name
  - Order (position in sequence)
  - Start time (datetime picker)
  - End time (datetime picker)
- List view showing all steps with:
  - Status badges (Active, Past, Upcoming)
  - Time range display
  - Edit and delete actions
- Methods: `addStep()`, `editStep()`, `updateStep()`, `deleteStep()`, `cancelEdit()`

### Contest Manager (`resources/views/livewire/contests/manager.blade.php`)
- Added a "Steps & Waves" link (list icon) for each contest
- Link navigates to the steps management page

## Routes

### New Route
Added in `routes/web.php`:
```php
Route::get('/contests/{contest}/steps', function (Site $site, Contest $contest) {
    return view('contests.steps', compact('site', 'contest'));
})->middleware('can:edit_areas,site')->name('contests.steps');
```

## Views

### Contest Steps View (`resources/views/contests/steps.blade.php`)
New blade template that renders the steps manager component

## Tests

### ContestFeaturesTest.php
Comprehensive test suite covering:
1. **Points in pivot table**: Tests that routes can have custom points
2. **Default points**: Verifies routes default to 100 points when not specified
3. **Contest steps**: Tests creating and associating steps with contests
4. **Step status methods**: Validates active/past/future status detection
5. **Dynamic points calculation**: Tests that points are divided by climber count
6. **No climbers scenario**: Verifies base points returned when no one has climbed the route

All tests pass successfully.

## Usage Examples

### Setting Route Points
1. Navigate to Admin > Site > Contests
2. Click the "Routes" icon for a contest
3. Select routes to include in the contest
4. For each selected route, adjust the points value (default: 100)
5. Changes are saved automatically

### Managing Contest Steps
1. Navigate to Admin > Site > Contests
2. Click the "Steps & Waves" icon (list icon) for a contest
3. Fill in the form with step details:
   - Name: e.g., "Pre-qualification Wave 1"
   - Order: 0 (first step), 1 (second step), etc.
   - Start/End times
4. Click "Add Step"
5. Steps appear in the list with their status
6. Edit or delete steps as needed

### Dynamic Points Calculation
The `getRoutePoints($routeId)` method automatically calculates dynamic points:
- Base points: 300
- 3 climbers complete the route
- Each climber earns: 300 / 3 = 100 points

## Benefits

1. **Flexible Scoring**: Organizers can assign different point values to routes based on difficulty or importance
2. **Fair Competition**: Dynamic points prevent early climbers from having unfair advantages
3. **Complex Events**: Multi-step contests support various competition formats like qualifications and finals
4. **Time-Boxed Waves**: Different groups can compete in specific time slots

## Backward Compatibility

- Existing contests continue to work without changes
- Routes without explicit points default to 100 points
- Contests without steps function normally
- All existing tests continue to pass
