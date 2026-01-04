<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            
            // Technical analysis metrics
            $table->decimal('consistency_variance', 8, 2)->nullable()->comment('Variance of difficulty levels');
            $table->decimal('flash_work_ratio', 8, 2)->nullable()->comment('Ratio of flash to work ascents');
            $table->decimal('risk_profile_abandonment_rate', 8, 2)->nullable()->comment('Abandonment rate on challenging routes');
            $table->decimal('avg_difficulty_abandoned', 8, 2)->nullable()->comment('Average difficulty of abandoned routes');
            $table->integer('long_routes_count')->default(0)->comment('Count of long routes completed');
            $table->integer('short_routes_count')->default(0)->comment('Count of short boulder problems completed');
            $table->decimal('avg_time_between_attempts', 8, 2)->nullable()->comment('Average time between attempts on same route (hours)');
            
            // Movement type preferences (stored as JSON for flexibility)
            $table->json('movement_preferences')->nullable()->comment('Preferred movement types based on tags');
            
            // Behavioral analysis metrics
            $table->string('preferred_climbing_hour')->nullable()->comment('Most common climbing hour');
            $table->decimal('avg_session_duration', 8, 2)->nullable()->comment('Average session duration in hours');
            $table->decimal('avg_routes_per_session', 8, 2)->nullable()->comment('Average routes per session');
            $table->decimal('exploration_ratio', 8, 2)->nullable()->comment('Percentage of new vs repeated routes');
            $table->json('sector_fidelity')->nullable()->comment('Most climbed sectors');
            $table->decimal('avg_attempts_before_success', 8, 2)->nullable()->comment('Average attempts before success');
            $table->integer('project_count')->default(0)->comment('Number of multi-session projects');
            
            // Progression analysis metrics
            $table->decimal('progression_rate', 8, 2)->nullable()->comment('Level progression per month');
            $table->boolean('plateau_detected')->default(false)->comment('Whether stagnation is detected');
            $table->integer('plateau_weeks')->default(0)->comment('Number of weeks in plateau');
            $table->json('progression_by_style')->nullable()->comment('Progression per climbing style');
            $table->json('progression_by_sector')->nullable()->comment('Progression per sector');
            
            // Training load analysis metrics
            $table->decimal('weekly_volume', 8, 2)->nullable()->comment('Weekly total climbing volume');
            $table->decimal('weekly_intensity', 8, 2)->nullable()->comment('Average weekly intensity');
            $table->decimal('acute_load', 8, 2)->nullable()->comment('Load of last 7 days');
            $table->decimal('chronic_load', 8, 2)->nullable()->comment('Load of last 28 days');
            $table->decimal('acute_chronic_ratio', 8, 2)->nullable()->comment('Acute/Chronic load ratio');
            $table->boolean('overtraining_detected')->default(false)->comment('Whether overtraining is detected');
            $table->decimal('avg_recovery_time', 8, 2)->nullable()->comment('Average time between sessions (hours)');
            $table->decimal('avg_time_between_performances', 8, 2)->nullable()->comment('Average time between big performances (hours)');
            
            $table->timestamp('last_calculated_at')->nullable()->comment('Last time stats were calculated');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_stats');
    }
};
