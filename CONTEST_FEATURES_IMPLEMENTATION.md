# Contest Features Implementation Summary

This document describes the new contest features that have been implemented.

## Overview

Three major features have been added to the contest management system:

1. **Bulk Import Users** - Import multiple users from a CSV file
2. **Authorized Users** - Restrict contest participation to specific users
3. **Grid Registration UI** - Quick registration interface for staff in official contests

## Feature Details

### 1. Bulk Import Users

**Purpose**: Allow administrators to quickly add multiple users to the system by uploading a CSV file.

**How to use**:
1. Navigate to the contest's "Authorized Users" page
2. Prepare a CSV file with two columns: `name` and `email`
3. Click "Choose File" and select your CSV
4. Click "Import"

**CSV Format**:
```csv
name,email
John Doe,john.doe@example.com
Jane Smith,jane.smith@example.com
```

**Behavior**:
- If a user with the email already exists in the database, they will NOT be recreated
- New users will be created with a random password (they can reset it via the forgot password feature)
- All imported users are automatically added to the contest's authorized users list
- Import results are displayed showing: users created, existing users found, and users authorized

### 2. Authorized Users

**Purpose**: Restrict contest access to only specific users.

**How to use**:
1. Navigate to Admin → Sites → [Your Site] → Contests
2. For an official contest, click on the "Authorized Users" icon (person with plus sign)
3. You can add users in two ways:
   - **Individual**: Search for a user by name or email and click "Add"
   - **Bulk**: Upload a CSV file (see above)

**Features**:
- View all authorized users in a table
- Remove individual users from the authorized list
- Clear all authorized users at once
- If no authorized users are set, ALL users can participate (open contest)
- If authorized users are set, ONLY those users can:
  - Appear in rankings
  - Access team features (if teams are enabled)
  - Be shown in the grid registration interface

**Navigation**: From the contest manager, click the user icon with a plus sign next to the Staff icon.

### 3. Grid Registration UI

**Purpose**: Provide a fast, visual interface for staff to register climbs in official contests.

**How to use**:
1. Navigate to Admin → Sites → [Your Site] → Contests
2. For an official contest, click on the "Grid Registration" icon (grid/table icon)
3. You'll see a table where:
   - Each row represents an authorized user
   - Each column represents a route in the contest
   - Click any cell to register/unregister a climb
   - Green checkmarks indicate completed climbs

**Features**:
- Quick registration: just click cells to toggle climbs
- Visual feedback with checkmarks
- Automatic verification (logged as verified by the current staff member)
- Only shows authorized users (if restrictions are set)
- Only available for official contests
- Routes are sorted by name
- Shows route grades in column headers

**Navigation**: From the contest manager, click the grid icon (last icon) in the row for your contest.

## Database Changes

A new table `contest_authorized_users` has been created with the following structure:
- `id`: Primary key
- `contest_id`: Foreign key to contests table
- `user_id`: Foreign key to users table
- `created_at`, `updated_at`: Timestamps
- Unique constraint on (contest_id, user_id) to prevent duplicates

## Model Changes

The `Contest` model has been enhanced with:
- `authorizedUsers()` - Relationship to get all authorized users
- `isUserAuthorized(User $user)` - Check if a user is authorized
- `addAuthorizedUser(User $user)` - Add a user to authorized list
- `removeAuthorizedUser(User $user)` - Remove a user from authorized list
- Updated `getRankingForStep()` to filter by authorized users

## Routes Added

Two new admin routes:
1. `/admin/site/{site}/contests/{contest}/authorized-users` - Manage authorized users
2. `/admin/site/{site}/contests/{contest}/grid-registration` - Grid registration interface

## Views Added

New Livewire components:
1. `contests.authorized-users` - Full-featured UI for managing authorized users and CSV import
2. `contests.grid-registration` - Grid-based registration interface

## Security Considerations

All implemented features follow Laravel best practices:
- CSRF protection on all forms
- File upload validation (CSV only, max 2MB)
- Email validation using Laravel's validator
- Authorization checks using existing policies
- SQL injection protection via Eloquent ORM
- XSS protection via Blade templating

## Testing

Five new tests have been added in `tests/Feature/ContestAuthorizedUsersTest.php`:
- Contest can have authorized users
- Contest can check if user is authorized
- Contest with no authorized users allows all users
- Can remove authorized user from contest
- Rankings are filtered by authorized users

All tests pass successfully.

## Usage Example

**Scenario**: You're organizing a climbing competition and received registrations from an external platform.

1. Export users from the external platform as CSV (name, email)
2. Go to your contest's "Authorized Users" page
3. Upload the CSV file
4. The system creates user accounts for new climbers and authorizes them all
5. During the competition, staff can use the "Grid Registration" interface
6. Click on cells to quickly mark which routes each climber completed
7. Rankings automatically update and only show authorized climbers

## Support

For issues or questions, please refer to the main documentation or contact the development team.
