<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update contests table - change team_mode from boolean to string
        // For SQLite, we need to recreate the table
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite doesn't support ALTER COLUMN, so we need to handle this differently
            // For existing databases, we'll do a data migration
            Schema::table('contests', function (Blueprint $table) {
                $table->string('team_mode_new', 20)->nullable()->after('use_dynamic_points');
            });
            
            // Migrate data: true -> 'register', false/null -> null
            DB::statement("UPDATE contests SET team_mode_new = CASE WHEN team_mode = 1 THEN 'register' ELSE NULL END");
            
            // Drop old column and rename new one
            Schema::table('contests', function (Blueprint $table) {
                $table->dropColumn('team_mode');
            });
            
            Schema::table('contests', function (Blueprint $table) {
                $table->renameColumn('team_mode_new', 'team_mode');
            });
        } elseif ($driver === 'pgsql') {
            // PostgreSQL syntax
            DB::statement("ALTER TABLE contests ALTER COLUMN team_mode TYPE VARCHAR(20)");
            DB::statement("UPDATE contests SET team_mode = CASE WHEN team_mode = 'true' THEN 'register' ELSE NULL END");
        } else {
            // MySQL syntax
            DB::statement("ALTER TABLE contests MODIFY COLUMN team_mode VARCHAR(20) NULL");
            DB::statement("UPDATE contests SET team_mode = CASE WHEN team_mode = '1' THEN 'register' ELSE NULL END");
        }

        // Add new columns to teams table
        Schema::table('teams', function (Blueprint $table) {
            $table->integer('max_users')->default(5)->after('name');
            $table->foreignId('created_by')->nullable()->after('max_users')->constrained('users')->onDelete('set null');
            $table->string('invitation_token', 64)->nullable()->unique()->after('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert teams table changes
        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn(['max_users', 'created_by', 'invitation_token']);
        });

        // Revert contests table changes
        $driver = DB::connection()->getDriverName();
        
        if ($driver === 'sqlite') {
            Schema::table('contests', function (Blueprint $table) {
                $table->boolean('team_mode_new')->default(false)->after('use_dynamic_points');
            });
            
            DB::statement("UPDATE contests SET team_mode_new = CASE WHEN team_mode = 'register' THEN 1 ELSE 0 END");
            
            Schema::table('contests', function (Blueprint $table) {
                $table->dropColumn('team_mode');
            });
            
            Schema::table('contests', function (Blueprint $table) {
                $table->renameColumn('team_mode_new', 'team_mode');
            });
        } elseif ($driver === 'pgsql') {
            // PostgreSQL syntax
            DB::statement("UPDATE contests SET team_mode = CASE WHEN team_mode = 'register' THEN 'true' ELSE 'false' END");
            DB::statement("ALTER TABLE contests ALTER COLUMN team_mode TYPE BOOLEAN USING team_mode::boolean");
            DB::statement("ALTER TABLE contests ALTER COLUMN team_mode SET DEFAULT false");
        } else {
            // MySQL syntax
            DB::statement("UPDATE contests SET team_mode = CASE WHEN team_mode = 'register' THEN '1' ELSE '0' END");
            DB::statement("ALTER TABLE contests MODIFY COLUMN team_mode TINYINT(1) NOT NULL DEFAULT 0");
        }
    }
};
