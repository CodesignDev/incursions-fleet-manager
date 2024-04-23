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
        Schema::create('gice_group_member', function (Blueprint $table) {
            $table->uuidId();
            $table->foreignId('user_id');
            $table->foreignId('gice_group_id');
            $table->boolean('is_primary_group')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gice_group_member');
    }
};
