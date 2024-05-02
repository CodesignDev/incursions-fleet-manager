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
        Schema::table('fleets', function (Blueprint $table) {
            $table->dropColumn('unlisted');
            $table->dropColumn('listed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fleets', function (Blueprint $table) {
            $table->after('untracked', function (Blueprint $table) {
                $table->boolean('unlisted')->nullable();
                $table->timestamp('listed_at')->nullable();
            });
        });
    }
};
