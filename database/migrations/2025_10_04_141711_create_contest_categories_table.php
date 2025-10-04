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
        Schema::create('contest_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('type')->nullable(); // 'age', 'gender', 'custom'
            $table->string('criteria')->nullable(); // e.g., '18-25', 'male', 'female', etc.
            $table->timestamps();
        });

        // Pivot table for users in categories
        Schema::create('contest_category_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['contest_category_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contest_category_user');
        Schema::dropIfExists('contest_categories');
    }
};
