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
            $table->after('exempt_from_fleet_warp', function ($table) {
                $table->string('joined_via')->nullable();
                $table->foreignId('invite_id')->nullable();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fleet_members', function (Blueprint $table) {
            $table->dropColumn('joined_via');
            $table->dropColumn('invite_id');
        });
    }
};
