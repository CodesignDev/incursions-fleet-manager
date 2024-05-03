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
        Schema::create('fleet_invites', function (Blueprint $table) {
            $table->uuidId();
            $table->foreignUuid('fleet_id');
            $table->foreignId('character_id');
            $table->foreignId('invited_by_id');
            $table->string('state');
            $table->timestamps();
            $table->timestamp('invite_sent_at')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fleet_invites');
    }
};
