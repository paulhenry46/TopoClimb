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
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->timestamp('removing_at')->nullable();
            $table->foreignId('line_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug');
            $table->integer('local_id');
            $table->string('comment')->nullable();
            $table->smallInteger('grade');
            $table->string('color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
