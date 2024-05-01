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
        Schema::create('fleet_members', function (Blueprint $table) {
            $table->uuidId();
            $table->foreignUuid('fleet_id');
            $table->foreignId('character_id');
            $table->boolean('fleet_boss');
            $table->boolean('exempt_from_fleet_warp');
            $table->timestamps();
            $table->timestamp('joined_at')->nullable();
            $table->softDeletes('left_at'); // deleted_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleet_members');
    }
};
