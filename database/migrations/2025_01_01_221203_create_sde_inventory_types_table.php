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
        Schema::create('sde_inventory_types', function (Blueprint $table) {
            $table->staticId('type_id');
            $table->foreignId('group_id');
            $table->foreignId('meta_group_id')->nullable();
            $table->foreignId('market_group_id')->nullable();
            $table->foreignId('faction_id')->nullable();
            $table->foreignId('race_id')->nullable();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('published');
            $table->float('mass')->nullable();
            $table->float('volume')->nullable();
            $table->float('packaged_volume')->nullable();
            $table->unsignedBigInteger('capacity')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sde_inventory_types');
    }
};
