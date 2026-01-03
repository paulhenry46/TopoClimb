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
        Schema::table('logs', function (Blueprint $table) {
            // Change the enum to include 'tentative'
            $table->enum('type', ['work', 'flash', 'view', 'tentative'])->change();
            // Add is_public column (default true for existing logs, false for tentative)
            $table->boolean('is_public')->default(true)->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropColumn('is_public');
            $table->enum('type', ['work', 'flash', 'view'])->change();
        });
    }
};
