<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if contest_user table exists
        if (!Schema::hasTable('contest_user')) {
            return;
        }

        // Get all contest staff relationships
        $staffRelations = DB::table('contest_user')->get();
        
        foreach ($staffRelations as $relation) {
            // Get the contest
            $contest = DB::table('contests')->where('id', $relation->contest_id)->first();
            
            if (!$contest) {
                continue;
            }
            
            // Only migrate staff for official contests
            if ($contest->mode === 'official') {
                $permissionName = 'contest.' . $contest->id;
                
                // Create permission if it doesn't exist
                $permission = Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web'
                ]);
                
                // Give permission to user
                DB::table('model_has_permissions')->insertOrIgnore([
                    'permission_id' => $permission->id,
                    'model_type' => 'App\\Models\\User',
                    'model_id' => $relation->user_id
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We cannot reliably reverse this migration because we don't know which permissions
        // were created by the migration vs which were created manually after the migration.
        // The drop_contest_user_table migration handles recreating the table if needed.
    }
};
