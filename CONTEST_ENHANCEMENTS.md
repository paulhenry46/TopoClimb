# Contest Enhancement Features

This document describes the three major enhancements added to the contest system: Team Contests, Categories, and Route Selection per Contest Step.

## Overview

The contest system has been enhanced with the following features:
1. **Team Contests** - Users can form teams and compete together
2. **Categories** - Contest rankings can be organized by age, gender, or custom criteria
3. **Route Selection per Step** - Each contest step can have its own specific routes

## 1. Team Contests

### Features
- Enable team mode for any contest
- Create and manage multiple teams per contest
- Users can join/leave teams
- Two team scoring modes:
  - **Unique Routes**: Each route counts once, even if multiple team members climb it
  - **All Climbs**: Count all climbs by team members (route climbed by 3 members = 3x points)
- Users can only be in one team per contest

### Admin Usage

1. **Enable Team Mode**
   - When creating or editing a contest, check "Enable Team Mode"
   - This activates team features for the contest

2. **Choose Team Points Calculation**
   - When team mode is enabled, select the scoring mode:
     - **Unique Routes Only**: Each route counts once for the team (default)
     - **All Climbs**: Each team member's climb counts separately
   - Example with "All Climbs": Route 1 climbed by 3 members = 3× points, Route 2 climbed by 2 members = 2× points

3. **Manage Teams**
   - Navigate to Admin > Site > Contests
   - Click the team icon (👥) for a contest with team mode enabled
   - Create teams by clicking "Create Team"
   - Add/remove team members
   - Delete teams as needed

### User Experience

1. **Joining a Team**
   - Visit the contest public page
   - Switch to "Team" view mode
   - Browse available teams
   - Click "Join Team" to join
   - Only one team membership per contest is allowed

2. **Viewing Team Rankings**
   - Team rankings show all teams sorted by total points
   - Points calculation depends on the contest's team points mode:
     - **Unique mode**: Points from unique routes climbed by any team member
     - **All mode**: Sum of points from all climbs by all team members
   - In unique mode, if two team members climb the same route, points are only counted once
   - In all mode, each member's climb contributes to the team total

### Database Schema

**teams table:**
- `id` - Primary key
- `contest_id` - Foreign key to contests
- `name` - Team name
- `timestamps`

**team_user pivot table:**
- `team_id` - Foreign key to teams
- `user_id` - Foreign key to users
- Unique constraint on (team_id, user_id)

**contests table (team-related fields):**
- `team_mode` - Boolean, enables team features
- `team_points_mode` - String ('unique' or 'all'), determines scoring method

## 2. Categories

### Features
- Create unlimited categories per contest
- Categories can be based on age, gender, or custom criteria
- Users can join multiple categories
- Separate rankings for each category
- Category membership is optional

### Admin Usage

1. **Create Categories**
   - Navigate to Admin > Site > Contests
   - Click the categories icon (◆) for any contest
   - Click "Create Category"
   - Fill in:
     - **Name**: e.g., "Men 18-25", "Women Elite", "Youth"
     - **Type**: Age, Gender, or Custom (optional)
     - **Criteria**: Additional information (optional)

2. **Manage Categories**
   - View category participants
   - Edit category details
   - Delete categories

### User Experience

1. **Joining Categories**
   - Visit the contest public page
   - Switch to "Categories" view mode
   - Browse available categories
   - Click "Join" on categories you want to participate in
   - You can join multiple categories

2. **Viewing Category Rankings**
   - Select a category from the tabs
   - Rankings show only participants in that category
   - Rankings are re-calculated specifically for category members

### Database Schema

**contest_categories table:**
- `id` - Primary key
- `contest_id` - Foreign key to contests
- `name` - Category name
- `type` - Type: 'age', 'gender', or 'custom'
- `criteria` - Additional criteria description
- `timestamps`

**contest_category_user pivot table:**
- `contest_category_id` - Foreign key to contest_categories
- `user_id` - Foreign key to users
- Unique constraint on (contest_category_id, user_id)

## 3. Route Selection per Contest Step

### Features
- Each contest step can have specific routes assigned
- Routes are optional - steps use all contest routes if none assigned
- Easy route selection with hierarchical view (Area > Sector > Line > Routes)
- Route count shown for each step

### Admin Usage

1. **Manage Contest Steps**
   - Navigate to Admin > Site > Contests
   - Click the steps icon (≡) for any contest
   - Create steps with name, order, and time period

2. **Assign Routes to a Step**
   - In the steps list, click "Manage Routes" for a step
   - A modal shows all contest routes organized by Area > Sector > Line
   - Check/uncheck routes to assign them to the step
   - Changes are saved automatically

3. **Route Assignment Behavior**
   - If a step has routes assigned, only those routes count for that step's ranking
   - If a step has no routes assigned, all contest routes are used
   - This allows flexibility for different contest formats

### User Experience

1. **Viewing Step Information**
   - Contest steps are shown as tabs on the contest page
   - Each step shows its name and status (Active/Upcoming/Past)
   - The route count displayed updates based on step-specific routes

2. **Rankings per Step**
   - Rankings are calculated only for routes assigned to that step
   - Time period is based on the step's start and end times
   - Overall ranking uses all contest routes and the full contest period

### Database Schema

**contest_step_route pivot table:**
- `id` - Primary key
- `contest_step_id` - Foreign key to contest_steps
- `route_id` - Foreign key to routes
- Unique constraint on (contest_step_id, route_id)
- `timestamps`

## Combined Usage Examples

### Example 1: Youth Team Competition
1. Create contest with team mode enabled
2. Create age category "Youth Under 16"
3. Create teams for different climbing gyms
4. Students join teams and the youth category
5. View rankings in:
   - Team mode: See which gym team is winning
   - Category mode: See youth rankings
   - Individual mode: See all participants

### Example 2: Multi-Stage Contest
1. Create contest with multiple steps:
   - "Qualification" (first 50 routes)
   - "Semi-Finals" (25 harder routes)
   - "Finals" (10 hardest routes)
2. Assign specific routes to each step
3. Create categories: "Men", "Women", "Youth"
4. Participants can:
   - See their qualification ranking
   - Advance to semi-finals based on qualification
   - View separate men/women/youth rankings

### Example 3: Age Group Competition
1. Create contest without team mode
2. Create categories:
   - "Under 14"
   - "14-17"
   - "18-29"
   - "30-44"
   - "45+"
3. Users join their appropriate age category
4. View separate rankings for each age group

## Technical Details

### Ranking Calculations

**Individual Rankings:**
- Sum of points from unique routes climbed
- Filtered by contest or step time period
- Filtered by official/free mode
- Sorted by total points descending

**Team Rankings:**
- **Unique mode (default)**: Sum of points from unique routes climbed by any team member
  - If multiple team members climb the same route, points counted once
- **All mode**: Sum of points from all routes climbed by all team members
  - Each team member's climb counted separately
  - Route climbed by N members = N × route points
- Filtered by contest time period and mode
- Sorted by total points descending

**Category Rankings:**
- Individual rankings filtered to only include category members
- Re-ranked within the category
- Same point calculation as individual rankings
- Same point calculation as individual rankings

### Step-Specific Routes
The ranking logic checks if a step has routes assigned:
```php
$routeIds = $step->routes->count() > 0 
    ? $step->routes->pluck('id')  // Use step routes
    : $this->contest->routes->pluck('id');  // Use all contest routes
```

## API/Livewire Methods

### Contest Model
- `getTeamRankingForStep($stepId = null)` - Get team rankings
- `getCategoryRankings($categoryId, $stepId = null)` - Get category-specific rankings
- `getRankingForStep($stepId = null)` - Enhanced to use step-specific routes

### Team Model
- `getTotalPoints()` - Calculate team's total points based on contest's team_points_mode
  - Returns sum of unique routes (default) or all climbs (when team_points_mode = 'all')

### Public View Component
- `setViewMode($mode)` - Switch between 'individual', 'team', 'category'
- `joinTeam($teamId)` - Join a team
- `leaveTeam($teamId)` - Leave a team
- `joinCategory($categoryId)` - Join a category
- `leaveCategory($categoryId)` - Leave a category

## Testing

Comprehensive test suite included in `tests/Feature/ContestEnhancementsTest.php`:
- Team mode functionality
- Team creation and user membership
- Team ranking calculations (unique mode)
- Team ranking calculations (all mode with duplicates)
- Category creation and user membership
- Category creation and user membership
- Route assignment to steps
- Team ranking calculations
- Category ranking filtering

Run tests with:
```bash
php artisan test --filter ContestEnhancementsTest
```

## Migration

All features are backward compatible. Existing contests will:
- Have `team_mode` set to `false` by default
- Have no teams or categories
- Have steps with no specific routes (use all contest routes)

To enable new features on existing contests:
1. Edit the contest and enable team mode if desired
2. Create categories through the categories manager
3. Assign routes to existing steps through the steps manager
