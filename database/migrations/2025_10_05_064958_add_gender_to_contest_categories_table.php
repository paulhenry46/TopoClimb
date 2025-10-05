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
            $table->string('gender')->nullable()->after('max_age');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contest_categories', function (Blueprint $table) {
            $table->dropColumn('gender');
        });
    }
};
