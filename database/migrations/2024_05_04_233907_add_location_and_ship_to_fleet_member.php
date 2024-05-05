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
        Schema::table('fleet_members', function (Blueprint $table) {
            $table->after('character_id', function (Blueprint $table) {
                $table->foreignId('location_id');
                $table->foreignId('ship_id');
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fleet_members', function (Blueprint $table) {
            $table->dropColumn('location_id');
            $table->dropColumn('ship_id');
        });
    }
};
