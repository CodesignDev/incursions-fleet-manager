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
        Schema::create('sde_factions', function (Blueprint $table) {
            $table->staticId();
            $table->foreignId('corporation_id');
            $table->foreignId('militia_corporation_id')->nullable();
            $table->foreignId('home_system_id')->nullable();
            $table->string('name');
            $table->text('short_description')->nullable();
            $table->longtext('description')->nullable();
            $table->float('size_factor')->nullable();
            $table->boolean('is_unique')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sde_factions');
    }
};
