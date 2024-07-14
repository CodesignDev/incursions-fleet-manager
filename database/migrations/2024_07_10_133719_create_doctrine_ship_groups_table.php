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
        Schema::create('doctrine_ship_groups', function (Blueprint $table) {
            $table->uuidId();
            $table->foreignUuid('doctrine_id');
            $table->string('name');
            $table->integer('display_order');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctrine_ship_groups');
    }
};
