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
        Schema::create('universe_npc_stations', function (Blueprint $table) {
            $table->staticId();
            $table->foreignId('system_id');
            $table->foreignId('type_id');
            $table->foreignId('operation_id');
            $table->foreignId('corporation_id');
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('universe_npc_stations');
    }
};
