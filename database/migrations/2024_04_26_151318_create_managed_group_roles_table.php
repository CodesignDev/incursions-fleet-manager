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
        Schema::create('managed_group_roles', function (Blueprint $table) {
            $table->uuidId();
            $table->foreignId('group_id');
            $table->foreignId('role_id');
            $table->boolean('prevent_manual_assignment');
            $table->boolean('auto_remove_role');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('managed_group_roles');
    }
};
