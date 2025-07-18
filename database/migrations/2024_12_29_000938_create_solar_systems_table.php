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
        Schema::create('universe_solar_systems', function (Blueprint $table) {
            $table->staticId();
            $table->unsignedBigInteger('constellation_id');
            $table->string('name');
            $table->float('security');
            $table->unsignedBigInteger('radius')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('universe_solar_systems');
    }
};
