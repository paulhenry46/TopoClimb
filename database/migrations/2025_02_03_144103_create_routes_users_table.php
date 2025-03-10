<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('route_user', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('route_id');
        $table->unsignedBigInteger('user_id');
    
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('route_user');
    }
};
