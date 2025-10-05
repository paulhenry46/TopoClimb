# How to Use the New Contest Staff Permission System

## For Application Users

### Adding Staff Members to a Contest

1. Navigate to **Admin > Site > Contests**
2. Click the "Staff" icon for the desired contest (only available for official contests)
3. Search for a user by name or email
4. Click "Add Staff Member"
5. The user will now have the `contest.{id}` permission and can:
   - Access the contest registration page
   - Scan QR codes to verify climbers

### Removing Staff Members

1. Navigate to the contest staff management page
2. Click "Remove" next to the staff member's name
3. The `contest.{id}` permission will be revoked from the user

## For Developers

### Permission Naming Convention

- Format: `contest.{contest_id}`
- Example: `contest.5` for contest with ID 5
- Guard: `web`

### Automatic Permission Management

Permissions are automatically managed by the Contest model:

```php
// Creating an official contest automatically creates the permission
$contest = Contest::create([
    'name' => 'My Contest',
    'mode' => 'official',
    // ... other fields
]);
// Permission 'contest.{id}' is now created

// Changing mode from official to free deletes the permission
$contest->update(['mode' => 'free']);
// Permission 'contest.{id}' is now deleted

// Deleting a contest deletes the permission
$contest->delete();
// Permission 'contest.{id}' is now deleted
```

### Manual Staff Management

```php
// Add a staff member
$contest->addStaffMember($user);

// Remove a staff member
$contest->removeStaffMember($user);

// Check if user is a staff member
if ($contest->isStaffMember($user)) {
    // User is staff
}

// Get all staff members
$staffMembers = $contest->staffMembers()->get();
```

### Access Control

Staff members can access contest registrations through the policy:

```php
// In routes/web.php
Route::get('/contests/{contest}/registrations', ...)
    ->middleware('can:access_registrations,contest');

// In ContestPolicy.php
public function access_registrations(User $user, Contest $contest): bool
{
    // Staff members have access via contest permission
    if ($contest->mode === 'official') {
        $permissionName = 'contest.' . $contest->id;
        if ($user->hasPermissionTo($permissionName)) {
            return true;
        }
    }
    
    // Site admins/openers also have access
    return $user->can('areas.' . $contest->site_id);
}
```

### Important Notes

1. **Only Official Contests**: Permissions are only created for contests with `mode = 'official'`
2. **Free Contests**: Free contests cannot have staff members (permission-based)
3. **Automatic Cleanup**: Permissions are automatically deleted when:
   - Contest is deleted
   - Contest mode changes from official to free
4. **Migration Handled**: Existing staff relationships are automatically migrated from the old pivot table

## Checking Permissions in Blade Views

```blade
@can('access_registrations', $contest)
    <!-- User can access registrations -->
    <a href="{{ route('contests.registrations', ['site' => $site, 'contest' => $contest]) }}">
        Manage Registrations
    </a>
@endcan
```

## Checking Permissions in Controllers/Components

```php
// Check if current user can access registrations
if (auth()->user()->can('access_registrations', $contest)) {
    // User has access
}

// Check if current user is a staff member
if ($contest->isStaffMember(auth()->user())) {
    // User is staff
}
```

## Database Query

To find all staff members for a contest:

```php
// Using the model method (recommended)
$staffMembers = $contest->staffMembers()->get();

// Or directly via Permission
$permission = Permission::where('name', 'contest.' . $contest->id)->first();
$staffMembers = $permission ? $permission->users : collect();
```

## Troubleshooting

### Staff member can't access registrations
1. Verify the contest is in 'official' mode
2. Check if the permission exists: `Permission::where('name', 'contest.{id}')->exists()`
3. Check if the user has the permission: `$user->hasPermissionTo('contest.{id}')`

### Permission not created when contest created
1. Verify the contest mode is 'official' (free contests don't get permissions)
2. Check the model events are firing (booted method in Contest.php)

### Old staff members still have access
1. Run the data migration again if needed
2. Manually revoke permissions: `$user->revokePermissionTo('contest.{id}')`
