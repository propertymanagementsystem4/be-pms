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
        Schema::create('Property', function (Blueprint $table) {
            $table->uuid('id_property')->primary();
            $table->string('name');
            $table->string('description');
            $table->string('location');
            $table->integer('total_rooms');
            $table->string('property_code')->unique();
            $table->string('city');
            $table->string('province');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Property');
    }
};