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
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // Unique identifier like 'max_grade_6a'
            $table->string('name'); // Display name
            $table->text('description'); // Description of the achievement
            $table->string('type'); // Type: 'max_grade', 'total_routes', 'grade_count', 'contest', etc.
            $table->json('criteria')->nullable(); // JSON criteria for evaluation
            $table->foreignId('contest_id')->nullable()->constrained()->onDelete('cascade'); // For contest-specific achievements
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
