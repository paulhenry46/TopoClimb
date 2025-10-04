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
        Schema::table('contest_categories', function (Blueprint $table) {
            $table->boolean('auto_assign')->default(false)->after('criteria');
            $table->integer('min_age')->nullable()->after('auto_assign');
            $table->integer('max_age')->nullable()->after('min_age');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contest_categories', function (Blueprint $table) {
            $table->dropColumn(['auto_assign', 'min_age', 'max_age']);
        });
    }
};
