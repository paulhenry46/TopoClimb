# Contest Staff Migration to Permission System

## Summary

This migration replaces the pivot table approach for contest staff members with the Spatie Permission system that is already installed in the application.

## Changes Made

### 1. Contest Model (`app/Models/Contest.php`)
- **Removed**: `belongsToMany` relationship using `contest_user` pivot table
- **Added**: Methods to manage staff via permissions:
  - `addStaffMember(User $user)` - Adds contest.{id} permission to user
  - `removeStaffMember(User $user)` - Removes contest.{id} permission from user
  - `isStaffMember(User $user)` - Checks if user has contest.{id} permission
  - `createStaffPermission()` - Creates permission for official contests
  - `deleteStaffPermission()` - Deletes permission when contest is deleted or mode changes
  - `staffMembers()` - Returns query builder for users with contest permission
- **Added**: Model events (booted method) to automatically:
  - Create permission when official contest is created
  - Create permission when contest mode changes to official
  - Delete permission when contest mode changes from official to free
  - Delete permission when contest is deleted

### 2. User Model (`app/Models/User.php`)
- **Removed**: `contestStaff()` relationship method

### 3. Contest Policy (`app/Policies/ContestPolicy.php`)
- **Added**: New policy file with `access_registrations` method
- Allows access to contest registrations for:
  - Users with `contest.{id}` permission (staff members)
  - Users with `areas.{site_id}` permission (site admins/openers)

### 4. Routes (`routes/web.php`)
- **Updated**: Contest registrations route to use `can:access_registrations,contest` middleware
- **Updated**: User QR code route to check for any `contest.*` permission

### 5. Staff Management Component (`resources/views/livewire/contests/staff.blade.php`)
- **Updated**: `addStaff()` method to use `Contest::addStaffMember()`
- **Updated**: `removeStaff()` method to use `Contest::removeStaffMember()`
- **Updated**: `staffMembers()` computed property to call `staffMembers()->get()`

### 6. Database Migrations
- **Added**: `2025_10_05_155816_migrate_contest_staff_to_permissions.php`
  - Migrates existing staff relationships from `contest_user` to permissions
  - Only migrates staff for official contests
  - Creates `contest.{id}` permissions and assigns them to users
- **Added**: `2025_10_05_155900_drop_contest_user_table.php`
  - Drops the `contest_user` pivot table after data migration
  - Includes down() method to recreate table if needed

### 7. Tests
- **Updated**: `tests/Feature/ContestQrFeaturesTest.php` to use new methods
- **Added**: `tests/Feature/ContestStaffPermissionsTest.php` with 8 comprehensive tests:
  - Official contest creates permission on creation
  - Free contest does not create permission on creation
  - Adding staff member gives permission
  - Removing staff member revokes permission
  - Changing contest mode from official to free deletes permission
  - Deleting contest deletes permission
  - Staff members have access permission to contest registrations
  - Non-staff members do not have access permission to contest registrations

## Benefits

1. **Unified Permission System**: Uses the same Spatie Permission system already in place for other features
2. **Automatic Cleanup**: Permissions are automatically deleted when:
   - Contest is deleted
   - Contest mode changes from official to free
3. **Access Control**: Staff members can access registration pages using the `contest.{id}` permission without needing site-level permissions
4. **Backward Compatible**: Data migration ensures existing staff relationships are preserved
5. **Better Integration**: Staff permissions work seamlessly with the existing authorization system

## Migration Path

For existing databases:
1. Run migrations: `php artisan migrate`
2. The data migration will automatically:
   - Read existing staff relationships from `contest_user` table
   - Create `contest.{id}` permissions for official contests
   - Assign permissions to existing staff members
3. The `contest_user` table will be dropped after migration

## Testing

All 39 contest-related tests pass, including:
- 9 contest enhancement tests
- 9 contest feature tests
- 7 contest QR feature tests
- 8 new contest staff permission tests
- 5 general contest tests

## Permission Format

- Permission name: `contest.{contest_id}`
- Guard name: `web`
- Only created for contests with mode = 'official'
- Automatically managed by Contest model lifecycle events
