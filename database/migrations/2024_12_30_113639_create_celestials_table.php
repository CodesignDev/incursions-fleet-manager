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
        Schema::create('universe_celestials', function (Blueprint $table) {
            $table->staticId();
            $table->foreignId('system_id');
            $table->foreignId('orbital_id')->nullable();
            $table->foreignId('type_id');
            $table->string('celestial_type');
            $table->string('name');
            $table->integer('celestial_index')->nullable();
            $table->integer('orbital_index')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('universe_celestials');
    }
};
