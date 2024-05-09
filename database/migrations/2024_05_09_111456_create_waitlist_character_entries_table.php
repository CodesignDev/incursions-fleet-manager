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
        Schema::create('waitlist_character_entries', function (Blueprint $table) {
            $table->uuidId();
            $table->foreignUuid('waitlist_entry_id');
            $table->foreignId('character_id');
            $table->string('requested_ship')->nullable();
            $table->timestamps();
            $table->softDeletes('removed_at');
            $table->foreignId('removed_by')->nullable();
            $table->string('removal_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waitlist_character_entries');
    }
};
