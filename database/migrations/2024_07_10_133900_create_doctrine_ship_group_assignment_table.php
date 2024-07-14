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
        Schema::create('doctrine_ship_group_assignment', function (Blueprint $table) {
            $table->uuidId();
            $table->foreignUuid('doctrine_ship_group_id');
            $table->foreignUuid('doctrine_ship_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctrine_ship_group_assignment');
    }
};
