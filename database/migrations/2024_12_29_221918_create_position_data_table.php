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
        Schema::create('universe_position_data', function (Blueprint $table) {
            $table->uuidId();
            $table->morphs('positional');
            $table->positionCoordinates();
            $table->positionCoordinates('min', nullable: true);
            $table->positionCoordinates('max', nullable: true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('universe_position_data');
    }
};
