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
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('route_id')->constrained();
            $table->text('comment')->nullable();
            $table->string('video_url')->nullable();
            $table->smallInteger('grade');
            $table->enum('type', ['work', 'flash', 'view']);
            $table->enum('way', ['top-rope', 'lead', 'bouldering']);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
