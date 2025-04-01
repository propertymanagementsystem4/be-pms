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
        Schema::create('Facility', function (Blueprint $table) {
            $table->uuid('id_facility')->primary();
            $table->uuid('property_id');
            $table->string('name');
            $table->string('description');
            $table->double('price');
            $table->string('facility_code')->unique();
            $table->timestamps();

            $table->foreign('property_id')->references('id_property')->on('Property')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Facility');
    }
};