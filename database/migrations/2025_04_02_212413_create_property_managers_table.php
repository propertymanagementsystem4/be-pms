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
        Schema::create('PropertyManager', function (Blueprint $table) {
            $table->uuid('id_property_manager')->primary();
            $table->uuid('user_id');
            $table->uuid('property_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id_user')->on('User')->onUpdate('cascade');
            $table->foreign('property_id')->references('id_property')->on('Property')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('PropertyManager');
    }
};