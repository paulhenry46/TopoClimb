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
        Schema::table('contests', function (Blueprint $table) {
            // First, we need to handle the existing boolean column
            // Convert existing true/false to 'register'/null
            DB::statement("ALTER TABLE contests MODIFY COLUMN team_mode VARCHAR(20) NULL");
            DB::statement("UPDATE contests SET team_mode = CASE WHEN team_mode = '1' THEN 'register' ELSE NULL END");
        });

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
            $table->dropColumn(['max_users', 'created_by', 'invitation_token']);
        });

        // Revert contests table changes
        Schema::table('contests', function (Blueprint $table) {
            // Convert string back to boolean
            DB::statement("UPDATE contests SET team_mode = CASE WHEN team_mode = 'register' THEN '1' ELSE '0' END");
            DB::statement("ALTER TABLE contests MODIFY COLUMN team_mode TINYINT(1) NOT NULL DEFAULT 0");
        });
    }
};
