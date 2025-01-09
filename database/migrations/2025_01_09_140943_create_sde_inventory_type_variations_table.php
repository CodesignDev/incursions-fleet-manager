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
        Schema::create('sde_inventory_type_variations', function (Blueprint $table) {
            $table->foreignId('type_id');
            $table->foreignId('base_type_id');
            $table->foreignId('meta_group_id');
            $table->unsignedInteger('meta_level')->nullable();
            $table->timestamps();

            $table->primary(['type_id', 'base_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sde_inventory_type_variations');
    }
};
