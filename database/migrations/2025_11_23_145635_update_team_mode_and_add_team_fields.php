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
        Schema::table('contests', function (Blueprint $table) {
            $table->dropColumn('team_mode');
            $table->enum('team_mode', ['free', 'register', 'restricted']);
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
            $table->dropForeign(['created_by']);
            $table->dropColumn(['max_users', 'created_by', 'invitation_token']);
        });

         Schema::table('contests', function (Blueprint $table) {
            $table->dropColumn('team_mode');
            $table->boolean('team_mode')->default(false)->after('use_dynamic_points');
        });
    }
};
