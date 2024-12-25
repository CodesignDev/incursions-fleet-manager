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
        Schema::create('fleet_scans', function (Blueprint $table) {
            $table->uuidId();
            $table->foreignId('character_id');
            $table->unsignedBigInteger('fleet_id');
            $table->unsignedBigInteger('fleet_boss_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleet_scans');
    }
};
